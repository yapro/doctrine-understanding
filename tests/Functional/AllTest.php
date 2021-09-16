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

// https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/working-with-objects.html
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
	 * Опция cascade={"persist"} говорит о том, что когда объект Parent передан в $entityManager->persist(), то
	 * объект Kid автоматически будет передан в функцию $entityManager->persist()
	 */
	public function testCascadePersist()
	{
		$article = new Article();
		$article->setTitle('t1');

		$cascadePersistTrue = new CascadePersistTrue();
		$cascadePersistTrue->setMessage('m1');
		$cascadePersistTrue->setArticle($article);
		// недостаточно привязать Kid`s к Parent, нужно еще в Parent добавить Kid`s (ТУПАЯ doctrine):
		$article->getCascadePersistTrueCollection()->add($cascadePersistTrue);

		self::$entityManager->persist($article);
		self::$entityManager->flush();
		// Вуаля, мы не делали $entityManager->persist($cascadePersistTrue), doctrine сделала это за нас
		self::assertSame(1, $article->getId());
		self::assertSame(1, $cascadePersistTrue->getId());


		// проверим, что если нет cascade={"persist"}, то будет проблема:
		$cascadePersistFalse = new CascadePersistFalse();
		$cascadePersistFalse->setMessage('m1');
		$cascadePersistFalse->setArticle($article);
		// недостаточно привязать Kid`s к Parent, нужно еще в Parent добавить Kid`s (ТУПАЯ doctrine):
		$article->getCascadePersistFalseCollection()->add($cascadePersistFalse);
		// Дабы не мучиться, с этого момента в др. сущностях в методе setArticle() выполняется автопривязка Kid к Parent

		try {
			self::$entityManager->flush();
			$this->assertTrue(false);
		} catch (ORMInvalidArgumentException $exception) {
			// выбрасывается, когда к объекту Parent привязан объект Kid, про который UnitOfWork ничего не знает, потому
			// что не была вызвана EntityManager#persist(Kid) и для Kid не задана опция cascade={"persist"}
			$this->assertTrue(true);
		}
		// как видим $cascadePersistFalse в базу еще не попала:
		self::assertSame(0, $cascadePersistFalse->getId());
	}

	/**
	 * Опция cascade={"refresh"} говорит о том, что когда рефрешится Parent, то рефрешится и Kid
	 */
	public function testCascadeRefresh()
	{
		self::assertTrue(true);
	}

	/**
	 * Тестируем $entityManager->refresh(Parent);
	 */
	public function testRefresh()
	{
		$articleA = new Article();
		$articleA->setTitle('A');

		$cascadeRefreshFalse = new CascadeRefreshFalse();
		$cascadeRefreshFalse->setMessage('False');
		$cascadeRefreshFalse->setArticle($articleA);

		$cascadeRefreshTrue = new CascadeRefreshTrue();
		$cascadeRefreshTrue->setMessage('True');
		$cascadeRefreshTrue->setArticle($articleA);

		self::$entityManager->persist($articleA);
		self::$entityManager->flush();

		self::assertSame($articleA->getId(), $cascadeRefreshFalse->getArticle()->getId());
		self::assertSame($articleA->getId(), $cascadeRefreshTrue->getArticle()->getId());

		// НЕЖДАНЧИК 1:

		// удаляем Kid`s из Parent и ожидаем, что Kid`s теперь не смотрят на Parent
		$articleA->getCascadeRefreshFalseCollection()->removeElement($cascadeRefreshFalse);
		$articleA->getCascadeRefreshTrueCollection()->removeElement($cascadeRefreshTrue);
		// убедимся, что Parent уже ничего не знает про Kid`s
		self::assertSame(false, $articleA->getCascadeRefreshFalseCollection()->first());
		self::assertSame(false, $articleA->getCascadeRefreshTrueCollection()->first());
		// на всякий случай убедимся, что Parent не имеет Kid`s:
		self::assertSame(0, $articleA->getCascadeRefreshFalseCollection()->count());
		self::assertSame(0, $articleA->getCascadeRefreshTrueCollection()->count());
		// ЗАСАДА 1: Kid`s все равно смотрят на Parent
		self::assertSame($articleA->getId(), $cascadeRefreshFalse->getArticle()->getId());
		self::assertSame($articleA->getId(), $cascadeRefreshTrue->getArticle()->getId());
		// сохраняем данные в базу (будем надеяться, что doctrine исправит недоразумение):
		self::$entityManager->flush();
		// все так же Parent ничего не знает про Kid`s
		self::assertSame(false, $articleA->getCascadeRefreshFalseCollection()->first());
		self::assertSame(false, $articleA->getCascadeRefreshTrueCollection()->first());
		// все так же Parent не имеет Kid`s:
		self::assertSame(0, $articleA->getCascadeRefreshFalseCollection()->count());
		self::assertSame(0, $articleA->getCascadeRefreshTrueCollection()->count());
		// ЗАСАДА 2: Kid`s все равно смотрят на Parent ( $entityManager->flush() не помог )
		self::assertSame($articleA->getId(), $cascadeRefreshFalse->getArticle()->getId());
		self::assertSame($articleA->getId(), $cascadeRefreshTrue->getArticle()->getId());

		// НЕЖДАНЧИК 2:

		// вытащим Parent из бд
		self::$entityManager->refresh($articleA);
		// да, выше удалили Kid`s из Parent, но т.к. информация о связи находится в Kid`s, то ничего не отвязалось
		self::assertSame($cascadeRefreshFalse->getId(), $articleA->getCascadeRefreshFalseCollection()->current()->getId());
		self::assertSame($cascadeRefreshTrue->getId(), $articleA->getCascadeRefreshTrueCollection()->current()->getId());
		// отвязываем Kid`s от Parent
		$cascadeRefreshFalse->setArticle(null);
		$cascadeRefreshTrue->setArticle(null);
		// ожидаем, что после $entityManager->flush() к Parent точно не привязаны Kid`s
		self::$entityManager->flush();
		// ЗАСАДА 3: к Parent все таки привязаны Kid`s
		self::assertSame($cascadeRefreshFalse->getId(), $articleA->getCascadeRefreshFalseCollection()->first()->getId());
		self::assertSame($cascadeRefreshTrue->getId(),  $articleA->getCascadeRefreshTrueCollection()->first()->getId());

		// вытащим Parent из бд
		self::$entityManager->refresh($articleA);
		// Наконец Kid`s отвязались от Parent:
		self::assertSame(false, $articleA->getCascadeRefreshFalseCollection()->first());
		self::assertSame(false, $articleA->getCascadeRefreshTrueCollection()->first());
		/*
		ВЫВОДЫ:
		- удаляем Kid`s из Parent, обязательно удали Parent из Kid`s
		- если есть логика в бд, то лучше сразу после этого делай $entityManager->refresh(Parent);
		*/

		// НЕЖДАНЧИК 2:
//		$articleB = new Article();
//		$articleB->setTitle('B');
//
//		$cascadeRefreshFalse->setArticle($articleB);
//		$cascadeRefreshTrue->setArticle($articleB);
//
//		self::$entityManager->persist($articleB);
//		self::$entityManager->flush();
//
//		self::assertSame($articleB->getId(), $cascadeRefreshFalse->getArticle()->getId());
//		self::assertSame($articleB->getId(), $cascadeRefreshTrue->getArticle()->getId());
//
//		self::$entityManager->refresh($articleA);
//		self::$entityManager->refresh($articleB);
//
//		self::assertSame($articleB->getId(), $cascadeRefreshFalse->getArticle()->getId());
//		self::assertSame($articleB->getId(), $cascadeRefreshTrue->getArticle()->getId());

	}

	/**
	 * Опция orphanRemoval=true говорит о том, что когда удаляется объект Parent, то удаляется и объект Kid
	 */
	public function testOrphanRemoval()
	{
		$article = new Article();
		$article->setTitle('t1');

		$orphanRemovalFalse = new OrphanRemovalFalse();
		$orphanRemovalFalse->setMessage('m1');
		$orphanRemovalFalse->setArticle($article);

		$orphanRemovalTrue = new OrphanRemovalTrue();
		$orphanRemovalTrue->setMessage('m1');
		$orphanRemovalTrue->setArticle($article);

		self::$entityManager->persist($article);
		self::$entityManager->flush();

		self::assertSame(3, $article->getId());
		self::assertSame(1, $orphanRemovalTrue->getId());
		self::assertSame(1, $orphanRemovalFalse->getId());

		self::$entityManager->remove($article);
		self::$entityManager->flush();

		self::assertSame(null, $article->getId());
		self::assertSame(null, $orphanRemovalTrue->getId());
		self::assertSame(1, $orphanRemovalFalse->getId());
	}
}
