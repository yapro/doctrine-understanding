<?php

declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ReAddingToCollection
{
    /**
     * @var ?int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $id = 0; // ?int чтобы doctrine не падал при удалении записи

    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="reAddingToCollection")
     * @ORM\JoinColumn(name="articleId", nullable=true, onDelete="RESTRICT")
     */
    private ?Article $article;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Article|null
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * @param Article|null $article
     * @return ReAddingToCollection
     */
    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return ReAddingToCollection
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
}
