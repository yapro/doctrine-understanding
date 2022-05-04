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
    public function preFlushHandler(Calista $entity, LifecycleEventArgs $event)
    {
        $GLOBALS['testCrudEntity']['isPreFlushHandlerCalled'] = true;
    }

    /**
     * Метод вызывается, т.к. слушатель зарегистрирован в \YaPro\DoctrineUnderstanding\Tests\Functional\CommonTestCase::getEm()
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $GLOBALS['testCrudEntity']['isPostFlushCalled'] = true;
    }
}
