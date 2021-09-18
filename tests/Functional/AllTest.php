<?php
declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Functional;

use Doctrine\ORM\ORMInvalidArgumentException;
use YaPro\DoctrineUnderstanding\Tests\Entity\Article;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadePersistFalse;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadePersistTrue;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadeRefreshFalse;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadeRefreshTrue;
use YaPro\DoctrineUnderstanding\Tests\Entity\OrphanRemovalFalse;
use YaPro\DoctrineUnderstanding\Tests\Entity\OrphanRemovalTrue;

/**
 * Ищите в тесте слово НЕЖДАНЧИК и читайте пояснение.
 * https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/working-with-objects.html
 */
class AllTest extends CommonTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		self::truncateAllTables();
		// Если не делать clear(), то в UnitOfWork остается объект Article из первого теста в состоянии NEW с
		// несвязанным, но обязательным объектом OrphanRemovalFalse (phpunit старается сделать тесты идемпотентными)
		self::$entityManager->clear();
	}

	/**
	 * Тестируем удаление связей и изменение связей у Parent-а
	 *
	 * Тест специально написан на основании двух разных сущностей (CascadeRefreshFalse, CascadeRefreshTrue), чтобы
	 * показать, что наличие декларации cascade={"refresh"} никак не влияет на ситуацию.
	 *
	 * ИТОГ:
	 * 1. удаление в Parent-е связи с Kid`s, не влияет на Kid`s (Kid`s все еще остаются связанными с Parent-ом)
	 * 2. Удаление в Kid`s связи с Parent-ом, влияет на Parent-а, но только после $entityManager->refresh(Parent)
	 * 3. если есть логика в бд, то лучше сразу после $entityManager->flush() делать $entityManager->refresh(Parent)
	 */
	public function testRelations()
	{
		$article = new Article();

		$cascadeRefreshFalse = new CascadeRefreshFalse();
		$cascadeRefreshFalse->setArticle($article);

		$cascadeRefreshTrue = new CascadeRefreshTrue();
		$cascadeRefreshTrue->setArticle($article);

		self::$entityManager->persist($article);
		self::$entityManager->flush();



		// ТЕСТ 1: Удаление в Parent-е связи с Kid`s, не влияет на Kid`s (Kid`s все еще остаются связанными с Parent-ом)

		// убедимся, что Kid`s и Parent правильно связаны
		self::assertSame($article->getId(), $cascadeRefreshFalse->getArticle()->getId());
		self::assertSame($article->getId(), $cascadeRefreshTrue->getArticle()->getId());

		// удаляем Kid`s из Parent и ожидаем, что Kid`s теперь не смотрят на Parent
		$article->getCascadeRefreshFalseCollection()->removeElement($cascadeRefreshFalse);
		$article->getCascadeRefreshTrueCollection()->removeElement($cascadeRefreshTrue);

		$assert = function (Article $article, CascadeRefreshFalse $cascadeRefreshFalse, CascadeRefreshTrue $cascadeRefreshTrue) {
			// убедимся, что Kid`s смотрят на Parent
			self::assertSame($article->getId(), $cascadeRefreshFalse->getArticle()->getId());
			self::assertSame($article->getId(), $cascadeRefreshTrue->getArticle()->getId());

			// убедимся, что Parent уже ничего не знает про Kid`s
			self::assertSame(false, $article->getCascadeRefreshFalseCollection()->first());
			self::assertSame(false, $article->getCascadeRefreshTrueCollection()->first());

			// на всякий случай убедимся, что Parent не имеет Kid`s:
			self::assertSame(0, $article->getCascadeRefreshFalseCollection()->count());
			self::assertSame(0, $article->getCascadeRefreshTrueCollection()->count());
		};

		// ОЖИДАЕМО 1: если в Parent удалить связи с Kid`s, Kid`s все равно будут смотреть на Parent
		$assert($article, $cascadeRefreshFalse, $cascadeRefreshTrue);

		// сохраняем данные в базу (будем надеяться, что doctrine удалит связи из Kid`s):
		self::$entityManager->flush();

		// ОЖИДАЕМО 2: Kid`s все равно смотрят на Parent ( $entityManager->flush() не помог )
		$assert($article, $cascadeRefreshFalse, $cascadeRefreshTrue);

		// вытащим Parent из бд (будем надеяться, что doctrine удалит связи из Kid`s):
		self::$entityManager->refresh($article);

		// ОЖИДАЕМО 3: Kid`s все равно смотрят на Parent ( $entityManager->refresh() не помогает в удалении связей )
		self::assertSame(1, $article->getCascadeRefreshFalseCollection()->count());
		self::assertSame(1, $article->getCascadeRefreshTrueCollection()->count());



		// ТЕСТ 2: Удаление в Kid`s связи с Parent-ом, влияет на Parent-а

		// отвязываем Kid`s от Parent-а
		$cascadeRefreshFalse->setArticle(null);
		$cascadeRefreshTrue->setArticle(null);
		// ожидаем, что после $entityManager->flush() к Parent точно не привязаны Kid`s
		self::$entityManager->flush();

		// НЕЖДАНЧИК: к Parent все таки привязаны Kid`s
		self::assertSame($cascadeRefreshFalse->getId(), $article->getCascadeRefreshFalseCollection()->first()->getId());
		self::assertSame($cascadeRefreshTrue->getId(),  $article->getCascadeRefreshTrueCollection()->first()->getId());

		// вытащим Parent из бд
		self::$entityManager->refresh($article);

		// Наконец Kid`s отвязались от Parent-а:
		self::assertSame(false, $article->getCascadeRefreshFalseCollection()->first());
		self::assertSame(false, $article->getCascadeRefreshTrueCollection()->first());
	}
	/**
	 * Тестируем изменение связей
	 *
	 * Тест специально написан на основании двух разных сущностей (CascadeRefreshFalse, CascadeRefreshTrue), чтобы
	 * показать, что наличие декларации cascade={"refresh"} никак не влияет на ситуацию.
	 *
	 * ИТОГ: избежать ситуации "Parent связан с Kid`s, Kid`s связаны с Parent-2" помогает $entityManager->refresh(Parent)
	 */
	public function testRelationChanging()
	{
		$article = new Article();
		$article2 = new Article('Article2');

		$cascadeRefreshFalse = new CascadeRefreshFalse();
		$cascadeRefreshFalse->setArticle($article);

		$cascadeRefreshTrue = new CascadeRefreshTrue();
		$cascadeRefreshTrue->setArticle($article);

		self::$entityManager->persist($article);
		self::$entityManager->persist($article2);
		self::$entityManager->flush();

		// поменяем связи в Kid`s, и будем ожидать, что в Parent`е связь тоже изменится
		$cascadeRefreshFalse->setArticle($article2);
		$cascadeRefreshTrue->setArticle($article2);

		// НЕЖДАНЧИК 1: в Kid`s удалены связи с Parent, но Parent об этом ничего не знает
		self::assertEquals($article->getCascadeRefreshFalseCollection()->first()->getId(), $cascadeRefreshFalse->getId());
		self::assertEquals($article->getCascadeRefreshTrueCollection()->first()->getId(),  $cascadeRefreshTrue->getId());

		// проверим, может быть flush() поможет решить НЕЖДАНЧИК 1
		self::$entityManager->flush();

		// НЕЖДАНЧИК 2: flush() не помог (Parent связан с Kid`s, Kid`s связаны с Parent-2)
		self::assertEquals($article->getCascadeRefreshFalseCollection()->first()->getId(), $cascadeRefreshFalse->getId());
		self::assertEquals($article->getCascadeRefreshTrueCollection()->first()->getId(),  $cascadeRefreshTrue->getId());
		self::assertEquals($cascadeRefreshFalse->getArticle()->getId(), $article2->getId());
		self::assertEquals($cascadeRefreshTrue->getArticle()->getId(), $article2->getId());

		// проверим, может быть refresh() поможет решить НЕЖДАНЧИКИ
		self::$entityManager->refresh($article);

		self::assertSame($article->getCascadeRefreshFalseCollection()->first(), false);
		self::assertSame($article->getCascadeRefreshTrueCollection()->first(),  false);

		// проверим, что Kid`s связаны с Parent-2
		self::assertEquals($cascadeRefreshFalse->getArticle()->getId(), $article2->getId());
		self::assertEquals($cascadeRefreshTrue->getArticle()->getId(), $article2->getId());
	}


	/**
	 * Отсутствие опция cascade={"persist"} говорит о том, что когда объект Parent передан в $entityManager->persist(),
	 * то объект Kid не будет автоматически передан в функцию $entityManager->persist()
	 */
	public function testCascadePersistFalse()
	{
		$article = new Article();
		self::$entityManager->persist($article);

		// проверим, что если нет cascade={"persist"}, то будет проблема:
		$cascadePersistFalse = new CascadePersistFalse();
		$cascadePersistFalse->setArticle($article);

		// недостаточно привязать Kid`s к Parent, нужно еще в Parent добавить Kid`s (ТУПАЯ doctrine):
		$article->getCascadePersistFalseCollection()->add($cascadePersistFalse);
		// Дабы не мучиться, с этого момента в др. сущностях в методе setArticle() выполняется автопривязка Kid к Parent

		try {
			self::$entityManager->flush();
			$this->assertTrue(false);
		} catch (ORMInvalidArgumentException $exception) {
			// выбрасывается, когда к объекту Parent привязан объект Kid, про который UnitOfWork ничего не знает, потому
			// что не была вызвана EntityManager#persist(Kid) или для Kid не задана опция cascade={"persist"}
			$this->assertTrue(true);
		}
		// как видим Kid в базу еще не попала:
		self::assertSame(0, $cascadePersistFalse->getId());
	}

	/**
	 * Опция cascade={"persist"} говорит о том, что когда объект Parent передан в $entityManager->persist(), то
	 * объект Kid автоматически будет передан в функцию $entityManager->persist()
	 */
	public function testCascadePersistTrue()
	{
		$article = new Article();

		$cascadePersistTrue = new CascadePersistTrue();
		$cascadePersistTrue->setArticle($article);

		self::$entityManager->persist($article);
		self::$entityManager->flush();

		// Вуаля, мы не делали $entityManager->persist($cascadePersistTrue), doctrine сделала это за нас
		self::assertSame(1, $article->getId());
		self::assertSame(1, $cascadePersistTrue->getId());
	}

	/**
	 * Декларация cascade={"refresh"} говорит о том, что когда рефрешится Parent, то рефрешится и Kid
	 * НЕ РАБОТАЕТ: https://github.com/doctrine/orm/pull/6798
	 */
	public function testCascadeRefresh()
	{
		$article = new Article();

		$cascadeRefreshFalse = new CascadeRefreshFalse();
		$cascadeRefreshFalse->setMessage('Old');
		$cascadeRefreshFalse->setArticle($article);

		$cascadeRefreshTrue = new CascadeRefreshTrue();
		$cascadeRefreshTrue->setMessage('Old');
		$cascadeRefreshTrue->setArticle($article);

		self::$entityManager->persist($article);
		self::$entityManager->flush();

		self::$entityManager->getConnection()->executeQuery("UPDATE Article SET title='New'");
		self::$entityManager->getConnection()->executeQuery("UPDATE CascadeRefreshFalse SET message='New'");
		self::$entityManager->getConnection()->executeQuery("UPDATE CascadeRefreshTrue SET message='New'");
		self::$entityManager->refresh($article);
		// итак: Parent удален и заново выгружен из базы, Kid`s будут подгружены по запросу $parent->get...Collection()

		// ОЖИДАЕМО: существующий объект, который не должен был обновиться, все так же остался старым:
		self::assertSame('Old', $cascadeRefreshFalse->getMessage());
		// НЕЖДАНЧИК 1: существующий объект, который должен был обновиться, все так же остался старым:
		self::assertSame('Old', $cascadeRefreshTrue->getMessage());

		// ОЖИДАЕМО: существующий объект, который не должен был обновиться, все так же остался старым:
		self::assertSame('Old', $article->getCascadeRefreshFalseCollection()->first()->getMessage());
		// НЕЖДАНЧИК 2: doctrine должен был подгрузить данные из бд, но не сделал этого:
		self::assertSame('Old', $article->getCascadeRefreshTrueCollection()->first()->getMessage());

		// ОЖИДАЕМО: существующий объект, который не должен был обновиться, все так же остался старым:
		self::assertSame('Old', $cascadeRefreshFalse->getMessage());
		// НЕЖДАНЧИК 3: существующий объект, который должен был обновиться, все так же остался старым:
		self::assertSame('Old', $cascadeRefreshTrue->getMessage());

		// ОЖИДАЕМО: ранее созданный объект и привязанный объект - являются одним и тем же объектом:
		$article->getCascadeRefreshTrueCollection()->first()->setMessage('New2');
		self::assertSame('New2', $cascadeRefreshTrue->getMessage());
	}

	/**
	 * Опция orphanRemoval=true говорит о том, что когда удаляется объект Parent, то удаляется и объект Kid
	 */
	public function testOrphanRemoval()
	{
		$article = new Article();

		$orphanRemovalFalse = new OrphanRemovalFalse();
		$orphanRemovalFalse->setArticle($article);

		$orphanRemovalTrue = new OrphanRemovalTrue();
		$orphanRemovalTrue->setArticle($article);

		self::$entityManager->persist($article);
		self::$entityManager->flush();

		self::assertSame(1, $article->getId());
		self::assertSame(1, $orphanRemovalTrue->getId());
		self::assertSame(1, $orphanRemovalFalse->getId());

		self::$entityManager->remove($article);
		self::$entityManager->flush();

		self::assertSame(null, $article->getId());
		self::assertSame(null, $orphanRemovalTrue->getId());
		self::assertSame(1, $orphanRemovalFalse->getId());
	}
}
