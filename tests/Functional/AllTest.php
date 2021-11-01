<?php
declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Functional;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Query;
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
	 *
	 * Заметка: я ожидал, что flush актуализирует состояние согласно состоянию в бд или по крайней мере отвязывает все
	 * неправильно привязанные сущности (основываясь на PK связей), но она так не делает.
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
	 * Отсутствие cascade={"persist"} говорит о том, что когда объект Parent передан в $entityManager->persist(),
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
			// что не была вызвана EntityManager#persist(Kid) или для Kid не задана декларация cascade={"persist"}
			$this->assertTrue(true);
		}
		// как видим Kid в базу еще не попала:
		self::assertSame(0, $cascadePersistFalse->getId());
	}

	/**
	 * Декларация cascade={"persist"} говорит о том, что когда объект Parent передан в $entityManager->persist(), то
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
	 * Декларация orphanRemoval=true говорит о том, что когда удаляется объект Parent, то удаляется и объект Kid
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

        self::$entityManager->getConnection()->executeQuery("PRAGMA foreign_keys = OFF");
		self::$entityManager->remove($article);
		self::$entityManager->flush();
        self::$entityManager->getConnection()->executeQuery("PRAGMA foreign_keys = ON");

		self::assertSame(null, $article->getId());
		self::assertSame(null, $orphanRemovalTrue->getId());
		self::assertSame(1, $orphanRemovalFalse->getId());
	}

	/**
	 * ИТОГ:
	 * - $entityManager->flush() не синхронизирует состояние объектов с состоянием в бд ($entityManager->flush() лишь
	 *   сохраняет изменения внесенные между текущим $entityManager->flush() и предыдущим)
	 * - кажется, что стоит всегда использовать Query::HINT_REFRESH http://yapro.ru/article/5763 - но это неправильно,
	 *   доказательство этого находятся в testHintRefreshSubRelations()
	 */
	public function testHintRefresh()
	{
		// Обнаружилась ситуация, когда Doctrine в запросах возвращает данные отсутствующие в параметрах SQL-запроса
		// (возвращает лишние данные). Ситуация возникает потому что после выполнения SQL-запроса, Doctrine отправляет
		// данные в Unit of Work откуда данные возвращаются уже в виде объекта (или списка объектов). Казалось бы, все
		// хорошо, но проблема появляется, когда один и тот же объект/список объектов запрашивается из бд несколько раз.
		$article = new Article();
		(new CascadePersistTrue())->setArticle($article);
		(new CascadePersistTrue())->setArticle($article);
		self::$entityManager->persist($article);
		self::$entityManager->flush();

		// $result1 = Doctrine вытащи из бд покупателя с двумя последними заказами (LIMIT 2)
		// $result2 = Doctrine вытащи из бд покупателя и самый последний заказ (LIMIT 1)

		$limitOne = 1;

		$query = self::$entityManager
			->createQuery('SELECT a, c FROM ' . Article::class . ' a JOIN a.cascadePersistTrueCollection c')
			->setFirstResult(0)
			->setMaxResults($limitOne);

		$result = $query->getResult(AbstractQuery::HYDRATE_ARRAY);
		self::assertSame(
			'[{"id":1,"title":"Article","cascadePersistTrueCollection":[{"id":1,"parentId":0,"message":"True"}]}]',
			json_encode($result)
		);
		self::assertSame(1, count($result));
		self::assertSame($limitOne, count($result[0]['cascadePersistTrueCollection']));

		// НЕЖДАНЧИК 1: без AbstractQuery::HYDRATE_ARRAY возвращается неправильное кол-во Kid`s - не учитывается setMaxResults()
		$result = $query->getResult();
		self::assertSame($limitOne, count($result));
		self::assertSame(2, $result[0]->getCascadePersistTrueCollection()->count());
		// в ->setMaxResults() указали 1, а получили 2. Предположительно потому, что мы добавляли записи в базу с
		// помощью объектов, а doctrine зная информацию о связях, просто вытаскивает из "Unit of Work" доп. объекты,
		// игнорируя указание ->setMaxResults($limitOne)).



		// НЕЖДАНЧИК 2: если в Parent добавить новый Kid, то повторная выборка не дает новый результат (doctrine
		// кэширует результат запроса на основании SQL)
		self::$entityManager->getConnection()->executeQuery(
			"INSERT INTO CascadePersistTrue (parentId, message, articleId) VALUES (0, 'message', 1)"
		);
		$result = $query->getResult();
		self::assertSame(2, $result[0]->getCascadePersistTrueCollection()->count());
		// в ->setMaxResults() указали 1, а получили 2, а в таблице CascadePersistTrue уже 3 записи



		// НЕЖДАНЧИК 3: тот же самый запрос с HINT_REFRESH возвращает правильное кол-во Kid`s равное 1-ому
		$queryClone = (clone $query);
		$result = $queryClone->setHint(Query::HINT_REFRESH, true)->getResult();
		self::assertSame($limitOne, $result[0]->getCascadePersistTrueCollection()->count());
		// возвращает правильное кол-во, но непонятно почему первый запрос сразу не мог вернуть правильное кол-во

		// ОЖИДАЕМО: теперь и предыдущий запрос (без HINT_REFRESH и без AbstractQuery::HYDRATE_ARRAY) возвращает
		// правильное кол-во Kid`s равное 1-ому (потому что объект $queryClone все еще содержит Query::HINT_REFRESH)
		$result = $queryClone->getResult();
		self::assertSame($limitOne, $result[0]->getCascadePersistTrueCollection()->count());



		// НЕЖДАНЧИК 4: опа теперь "НЕЖДАНЧИК 1" работает правильно даже без AbstractQuery::HYDRATE_ARRAY
		$result = $query->getResult();
		self::assertSame(1, $result[0]->getCascadePersistTrueCollection()->count());
		// теперь возвращает правильное кол-во, но объяснения этому факту я найти не могу, разве что несмотря на то,
		// что при указании Query::HINT_REFRESH мы клонировали $query, Query::HINT_REFRESH все так же остался в $query
	}

	/**
	 * ИТОГ:
	 * 1. Query::HINT_REFRESH помогает справиться с неверным результатом последнего запроса, но меняет предыдущие
	 *    результаты, а значит использовать его нельзя, нужно вызывать $query->getResult(AbstractQuery::HYDRATE_ARRAY) и
	 *    затем при необходимости сериализовать массив в иммутабельные объекты
	 * 2. Doctrine приводит состояние объектов в предыдущих результатах, к состоянию указанному в $query, поэтому, если
	 * вам нужно неизменяемое состояние $query->getResult(), то клонируйте его или сериализуйте в простые структуры, или
	 * с осторожностью используйте $entityManager->clear(), ведь clear() отвяжет от ORM все что было привязано ранее
	 * (таким образом будет отвязано даже то, что не планировалось отвязывать) + при flush() не возникает ошибок и
	 * эксепшенов (объекты ведь отвязаны, первый раз такое поведение кажется неочевидным).
	 */
	public function testHintRefreshSubRelations()
	{
		// НЕЖДАНЧИК 1: если через точку с запятой объединить два SQL-запроса в один executeQuery() то первый
		// выполнится, а второй (и последующие) не выполняться + не выбросится никакого эксепшена, жесть
		self::$entityManager->getConnection()->executeQuery(
			"INSERT INTO Article (title) VALUES ('title-1'), ('title-2');"
		);
		self::$entityManager->getConnection()->executeQuery(
			"INSERT INTO CascadePersistTrue (parentId, message, articleId) VALUES (0, 'message-1', 1), (0, 'message-2', 1)"
		);
		$limitOne = 1;

		$query = self::$entityManager
			->createQuery('SELECT a, c FROM ' . Article::class . ' a JOIN a.cascadePersistTrueCollection c')
			->setFirstResult(0)
			->setMaxResults($limitOne);
		$result = $query->getResult();
		self::assertSame($limitOne, count($result));
		// НЕЖДАНЧИК 2: возвращается верное кол-во Kid`s (согласно setMaxResults), но в testHintRefresh возвращалось
		// неправильное кол-во записей. Предположительно потому, что мы добавляли записи в базу с помощью объектов, и
		// doctrine хранил информацию о связях и просто вытаскивал из "Unit of Work" доп. объекты, игнорируя
		// указание ->setMaxResults($limitOne)).
		self::assertSame(1, $result[0]->getCascadePersistTrueCollection()->count());
		// Ошибка ли это, нет, так задумано, Doctrine хранит уже полученные данные в виде объектов, и при повторном
		// запросе из бд, не удаляет то, что было получено ранее, а при возможности дополняет (такая стратегия работы).



		$result = $query->setMaxResults(2)->getResult();
		self::assertSame($limitOne, count($result));
		// НЕЖДАНЧИК 3: мы указали setMaxResults(2), а получаем одну запись
		self::assertSame(1, $result[0]->getCascadePersistTrueCollection()->count());
		// результат неправильный, а SQL-правильный - LIMIT 2:
		$sql = 'SELECT a0_.id AS id_0, a0_.title AS title_1, '.
			'c1_.id AS id_2, c1_.parentId AS parentId_3, c1_.message AS message_4, c1_.articleId AS articleId_5 '.
			'FROM Article a0_ INNER JOIN CascadePersistTrue c1_ ON a0_.id = c1_.articleId LIMIT 2';
		self::assertSame($sql, $query->getSQL());



		// НЕЖДАНЧИК 4: мы не меняем $result, doctrine сама это делает за нас
		$result2 = $query->setHint(Query::HINT_REFRESH, true)->getResult();
		self::assertSame(2, $result2[0]->getCascadePersistTrueCollection()->count());
		self::assertSame(2, $result[0]->getCascadePersistTrueCollection()->count());
		// теперь результат правильный, но содержимое переменной $result изменено (вот это поворот)

		$result3 = $query->setMaxResults(1)->setHint(Query::HINT_REFRESH, true)->getResult();
		self::assertSame($result3[0]->getCascadePersistTrueCollection()->count(), 1);
		// результат правильный, но посмотрите что будет дальше:
		// НЕЖДАНЧИК 5: doctrine всякий раз меняет содержимое результирующих переменных
		self::assertSame($result3[0]->getCascadePersistTrueCollection()->count(), $result[0]->getCascadePersistTrueCollection()->count());
		self::assertSame($result3[0]->getCascadePersistTrueCollection()->count(), $result2[0]->getCascadePersistTrueCollection()->count());
		// $result, $result2 снова содержит 1 объект CascadePersistTrue (на лицо перезаписанное значение переменной)



		// НЕЖДАНЧИК 6: Еще есть функция $entityManager->clear(); которая отвязывает от Unit of Work всё, что было ранее
		// привязано. Таким образом, если вызвать ее перед следующим запросом, предыдущие $result-ы останется не
		// тронутыми, а новый $result будет правильным (не будет содержать избыточно связанные объекты).
		self::$entityManager->clear();
		$result4 = $query->setMaxResults(2)->setHint(Query::HINT_REFRESH, true)->getResult();
		self::assertSame($result4[0]->getCascadePersistTrueCollection()->count(), 2);
		self::assertSame($result3[0]->getCascadePersistTrueCollection()->count(), 1);
		self::assertSame($result3[0]->getCascadePersistTrueCollection()->count(), $result[0]->getCascadePersistTrueCollection()->count());
		self::assertSame($result3[0]->getCascadePersistTrueCollection()->count(), $result2[0]->getCascadePersistTrueCollection()->count());
		// НЕЖДАНЧИК 7: Казалось бы вот оно счастье, но нет, т.к. предыдущие $result-ы отвязаны от "Unit of Work", то
		// любые изменения произведенные с предыдущими $result-ами не будут сохранены при flush().
		/** @var $result Article[] */
		/** @var $result4 Article[] */
		$result4[0]->setTitle('new value 4');
		$result[0]->setTitle('new value 0');
		self::$entityManager->flush();
		// итог: сохранено значение 'new value 4'
	}

    /**
     * ИТОГ:
     * В сценарии когда мы отдельно достаем дочерний объект и его родителя, а затем повторно добавим первый
     * в коллекцию второго, доктрина распознает, что добавление выполняется повторно и нового экземпляра добавлено не
     * будет.
     */
    public function testReAddingToCollection(): void
    {
        self::$entityManager->getConnection()->executeQuery(
            "INSERT INTO Article (title) VALUES ('title-1');"
        );
        self::$entityManager->getConnection()->executeQuery(
            "INSERT INTO CascadePersistTrue (parentId, message, articleId) VALUES (0, 'msg-kid-1', 1)"
        );

        $kid1 = self::$entityManager->getRepository(CascadePersistTrue::class)->findOneByMessage('msg-kid-1');
        $kid2 = (new CascadePersistTrue())->setMessage('msg-kid-2');

        /** @var Article $parent */
        $parent = self::$entityManager->getRepository(Article::class)->find(1);
        $parent->addCascadePersistTrue($kid1);
        $parent->addCascadePersistTrue($kid2);

        self::$entityManager->flush();
        self::$entityManager->clear();
        /** @var Article $parent */
        $parent = self::$entityManager->getRepository(Article::class)->find(1);
        $this->assertCount(2, $parent->getCascadePersistTrueCollection());
    }

    /**
     * ИТОГ:
     * В сценарии когда мы отдельно достаем дочерний объект, очищаем entityManager, достаем родителя, а затем
     * повторно добавим дочерний объект в коллекцию родителя, доктрина НЕ распознает, что добавление выполняется
     * повторно и сохранит в бд тот же экземпляр как отдельную запись.
     */
    public function testReAddingToCollectionWithClear(): void
    {
        self::$entityManager->getConnection()->executeQuery(
            "INSERT INTO Article (title) VALUES ('title-1');"
        );
        self::$entityManager->getConnection()->executeQuery(
            "INSERT INTO CascadePersistTrue (parentId, message, articleId) VALUES (0, 'msg-kid-1', 1)"
        );

        $kid1 = self::$entityManager->getRepository(CascadePersistTrue::class)->findOneByMessage('msg-kid-1');
        // !! сбросим entityManager
        self::$entityManager->clear();
        $kid2 = (new CascadePersistTrue())->setMessage('msg-kid-2');

        /** @var Article $parent */
        $parent = self::$entityManager->getRepository(Article::class)->find(1);
        $parent->addCascadePersistTrue($kid1);
        $parent->addCascadePersistTrue($kid2);

        self::$entityManager->flush();
        self::$entityManager->clear();
        /** @var Article $parent */
        $parent = self::$entityManager->getRepository(Article::class)->find(1);
        $this->assertCount(3, $parent->getCascadePersistTrueCollection());
    }

    /**
     * Итог:
     * Метод execute QueryBuilder'а выкидывает исключения на обновление, несмотря на то что это не прописано в его
     * аннотации.
     */
    public function testExecuteThrowsExceptionOnUpdate(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);

        $article0 = new Article('article 0');
        $article1 = new Article('article 1');
        self::$entityManager->persist($article0);
        self::$entityManager->persist($article1);
        self::$entityManager->flush();

        self::$entityManager->getRepository(Article::class)
            ->createQueryBuilder('a')
            ->update()
            ->set('a.title', ':newTitle')
            ->andWhere('a.id = :id')
            ->setParameter('id', $article1->getId())
            ->setParameter('newTitle', 'article 0')// такой же title, как и другой $article.
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Итог:
     * Метод execute QueryBuilder'а выкидывает исключения на удаление, несмотря на то что это не прописано в его
     * аннотации.
     */
    public function testExecuteTrowsExceptionOnDelete(): void
    {
        $this->expectException(DriverException::class);
        $article = new Article('article 0');
        (new OrphanRemovalFalse())->setArticle($article);

        self::$entityManager->persist($article);
        self::$entityManager->flush();

        self::$entityManager->getRepository(Article::class)
            ->createQueryBuilder('a')
            ->delete()
            ->andWhere('a.id = :id')
            ->setParameter('id', $article->getId())
            ->getQuery()
            ->execute();
    }
}
