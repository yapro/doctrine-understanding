<?php

declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Сущность создана, чтобы понять, какие события срабатывают при insert/update/delete. При этом, в
 * \YaPro\DoctrineUnderstanding\Tests\Functional\CommonTestCase::getEm() был дополнительно зарегистрирован подписчик на
 * событие postFlush (которое не срабатывает, если не объявлять данное событие вместе со слушателем.
 * Итог: https://yapro.ru/article/6071
 *
 * @ORM\Entity
 * @ORM\EntityListeners({"YaPro\DoctrineUnderstanding\Tests\EntityListener\CalistaListener"})
 */
class Calista
{
    /**
     * Идентификаторы назначаются (и, таким образом, генерируются) кодом приложения. Назначение должно быть выполнено
     * до того, как новая сущность будет передана в EntityManager#persist. ЭТО не то же самое, что полностью отказаться
     * от GeneratedValue.
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $id = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title = '';

    public function __construct(string $id, string $title = 'default title')
    {
        $this->id = $id;
        $this->title = $title;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
