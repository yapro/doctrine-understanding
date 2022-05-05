<?php

declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use YaPro\DoctrineUnderstanding\Tests\Entity\Calista;

class CalistaListener
{
    /**
     * Метод вызывается, т.к. слушатель зарегистрирован с помощью аннотации к сущности + аннотации ORM\PostPersist
     *
     * @ORM\PostPersist
     */
    public function insert(Calista $entity, LifecycleEventArgs $event)
    {
        $GLOBALS['CalistaListener']['isInsertCalled'] = true;
        $GLOBALS['CalistaListener']['handlePostPersist'][] = $entity;
    }

    /**
     * Метод вызывается, т.к. слушатель зарегистрирован с помощью аннотации к сущности + аннотации ORM\PostPersist
     *
     * @ORM\PostUpdate
     */
    public function update(Calista $entity, LifecycleEventArgs $event)
    {
        $GLOBALS['CalistaListener']['isUpdateCalled'] = true;
        $GLOBALS['CalistaListener']['handlePostUpdate'][] = $entity;
    }

    /**
     * Метод вызывается, т.к. слушатель зарегистрирован с помощью аннотации к сущности + аннотации ORM\PostPersist
     *
     * @ORM\PostRemove
     */
    public function delete(Calista $entity, LifecycleEventArgs $event)
    {
        $GLOBALS['CalistaListener']['isDeleteCalled'] = true;
        $GLOBALS['CalistaListener']['handlePostRemove'][] = $entity;
    }

    /**
     * Метод вызывается, т.к. слушатель зарегистрирован в \YaPro\DoctrineUnderstanding\Tests\Functional\CommonTestCase::getEm()
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $GLOBALS['CalistaListener']['isPostFlushCalled'] = true;
        // foreach ($args->getEntityManager()->getUnitOfWork()->getIdentityMap() as $entityName => $entityItems) { ... }
    }
}
