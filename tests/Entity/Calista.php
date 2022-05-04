<?php

declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
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
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $title = '';

    public function __construct(string $title = 'default title')
    {
        $this->title = $title;
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
