<?php
declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Functional;

use Doctrine\ORM\ORMInvalidArgumentException;
use YaPro\DoctrineUnderstanding\Tests\Entity\Article;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadePersistFalse;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadePersistTrue;
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
	 * Опция orphanRemoval=true говорит о том, что когда удаляется объект Parent, то удаляется и объект Kid
	 */
	public function testOrphanRemoval()
	{
		$article = new Article();
		$article->setTitle('t1');

		$orphanRemovalTrue = new OrphanRemovalTrue();
		$orphanRemovalTrue->setMessage('m1');
		$orphanRemovalTrue->setArticle($article);

		$orphanRemovalFalse = new OrphanRemovalFalse();
		$orphanRemovalFalse->setMessage('m1');
		$orphanRemovalFalse->setArticle($article);

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

		self::$entityManager->persist($article);
		self::$entityManager->flush();

		self::assertSame(2, $article->getId());
		self::assertSame(1, $cascadePersistTrue->getId());



		$cascadePersistFalse = new CascadePersistFalse();
		$cascadePersistFalse->setMessage('m1');
		$cascadePersistFalse->setArticle($article);
		try {
			self::$entityManager->flush();
			$this->assertTrue(false);
		} catch (ORMInvalidArgumentException $exception) {
			// выбрасывается, когда к объекту Parent привязан объект Kid, про который UnitOfWork ничего не знает, потому
			// что не была вызвана EntityManager#persist(Kid) и для Kid не задана опция cascade={"persist"}
			$this->assertTrue(true);
		}
		self::assertSame(0, $cascadePersistFalse->getId());
	}
}
