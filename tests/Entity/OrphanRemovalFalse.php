<?php

declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OrphanRemovalFalse
{
    /**
     * @var ?int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")Article
     */
    private ?int $id = 0; // ?int чтобы doctrine не падал при удалении записи

    /**
     * @ORM\Column(type="integer")
     */
    private int $parentId = 0;

    /**
     * @ORM\Column(type="text")Article
     */
    private string $message = 'False';

    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="orphanRemovalFalseCollection")
     * @ORM\JoinColumn(name="articleId", nullable=false, onDelete="RESTRICT")
     */
    private Article $article;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article = null, bool $updateRelation = true): self
    {
        $this->article = $article;
        if ($article && $updateRelation) {
            $article->addOrphanRemovalFalse($this, false);
        }

        return $this;
    }
}
