<?php

declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Functional;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use YaPro\DoctrineUnderstanding\Tests\Entity\Article;
use YaPro\DoctrineUnderstanding\Tests\Entity\Calista;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadePersistFalse;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadePersistTrue;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadeRefreshFalse;
use YaPro\DoctrineUnderstanding\Tests\Entity\CascadeRefreshTrue;
use YaPro\DoctrineUnderstanding\Tests\Entity\OrphanRemovalFalse;
use YaPro\DoctrineUnderstanding\Tests\Entity\OrphanRemovalTrue;
use YaPro\DoctrineUnderstanding\Tests\EntityListener\CalistaListener;

class CommonTestCase extends TestCase
{
    protected static EntityManagerInterface $entityManager;

    public static function setUpBeforeClass(): void
    {
        self::$entityManager = self::getEm();
        self::createSchema();
    }

    protected function setUp(): void
    {
        // exception во время flush может закрыть EntityManager, без возможности открыть его заново.
        if (!self::$entityManager->isOpen()) {
            self::$entityManager = self::getEm();
        }
    }

    private static function getEm(): EntityManagerInterface
    {
        AnnotationRegistry::loadAnnotationClass(Groups::class);
        AnnotationRegistry::loadAnnotationClass(MaxDepth::class);
        // https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/tutorials/getting-started.html#obtaining-the-entitymanager
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $entities = [__DIR__ . '/../Entity'];
        $isDevMode = true;
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;
        $config = Setup::createAnnotationMetadataConfiguration($entities, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
        // database configuration parameters
        $dbPath = __DIR__ . '/../../vendor/bin/db.sqlite';
        touch($dbPath);
        chmod($dbPath, 0777);
        $conn = [
            // https://www.sqlitetutorial.net/sqlite-commands/
            'driver' => 'pdo_sqlite',
            'path' => $dbPath,
        ];
        $em = EntityManager::create($conn, $config);
        $em->getEventManager()->addEventListener([Events::postFlush], new CalistaListener());

        return $em;
    }

    private static function createSchema()
    {
        $classes = [
            self::$entityManager->getClassMetadata(Article::class),
            self::$entityManager->getClassMetadata(CascadePersistFalse::class),
            self::$entityManager->getClassMetadata(CascadePersistTrue::class),
            self::$entityManager->getClassMetadata(CascadeRefreshFalse::class),
            self::$entityManager->getClassMetadata(CascadeRefreshTrue::class),
            self::$entityManager->getClassMetadata(OrphanRemovalFalse::class),
            self::$entityManager->getClassMetadata(OrphanRemovalTrue::class),
            self::$entityManager->getClassMetadata(Calista::class),
        ];
        $schemaTool = new SchemaTool(self::$entityManager);
        // you can drop the table like this if necessary

        self::$entityManager->getConnection()->executeQuery('PRAGMA foreign_keys = OFF');
        $schemaTool->dropSchema($classes);
        self::$entityManager->getConnection()->executeQuery('PRAGMA foreign_keys = ON');
        $schemaTool->createSchema($classes);
    }

    protected static function truncateAllTables()
    {
        $sql = 'PRAGMA foreign_keys = OFF;';
        foreach (self::$entityManager->getConnection()->getSchemaManager()->listTableNames() as $tableName) {
            $sql .= 'delete from ' . $tableName . ';';
            $sql .= 'DELETE FROM SQLITE_SEQUENCE WHERE name="' . $tableName . '";';
        }
        $sql .= 'PRAGMA foreign_keys = ON;';

        static::$entityManager->getConnection()->exec($sql);
    }
}
