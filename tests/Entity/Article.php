<?php

declare(strict_types=1);

namespace YaPro\DoctrineUnderstanding\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity()
 */
class Article
{
	/**
	 * ?int чтобы doctrine не падал при удалении записи, ведь объект не добавленный в базу имеет: id=0,
	 * добавленный: id=значение_из_бд, удаленный: id=null
	 *
	 * @var ?int
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private ?int $id = 0;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $title = '';

	/**
	 * @var Collection|CascadePersistFalse[]
	 * @ORM\OneToMany(targetEntity="CascadePersistFalse", mappedBy="article")
	 * @MaxDepth(1)
	 */
	private Collection $cascadePersistFalseCollection;

	/**
	 * @var Collection|CascadePersistTrue[]
	 * @ORM\OneToMany(targetEntity="CascadePersistTrue", mappedBy="article", cascade={"persist"})
	 * @MaxDepth(1)
	 */
	private Collection $cascadePersistTrueCollection;

	/**
	 * @var Collection|CascadeRefreshFalse[]
	 * @ORM\OneToMany(targetEntity="CascadeRefreshFalse", mappedBy="article", cascade={"persist"})
	 * @MaxDepth(1)
	 */
	private Collection $cascadeRefreshFalseCollection;

	/**
	 * @var Collection|CascadeRefreshTrue[]
	 * @ORM\OneToMany(targetEntity="CascadeRefreshTrue", mappedBy="article", cascade={"persist", "refresh"})
	 * @MaxDepth(1)
	 */
	private Collection $cascadeRefreshTrueCollection;

    /**
     * @var Collection|OrphanRemovalTrue[]
     * @ORM\OneToMany(targetEntity="OrphanRemovalTrue", mappedBy="article", cascade={"persist"}, orphanRemoval=true)
     * @MaxDepth(1)
     */
    private Collection $orphanRemovalTrueCollection;

	/**
	 * @var Collection|OrphanRemovalFalse[]
	 * @ORM\OneToMany(targetEntity="OrphanRemovalFalse", mappedBy="article", cascade={"persist"}, orphanRemoval=false)
	 * @MaxDepth(1)
	 */
	private Collection $orphanRemovalFalseCollection;

    /**
     * @var Collection|OrphanRemovalFalse[]
     * @ORM\OneToMany(targetEntity="ReAddingToCollection", mappedBy="article", cascade={"persist"})
     * @MaxDepth(1)
     */
    private Collection $reAddingToCollection;

    public function __construct(string $title = 'Article')
    {
		$this->title = $title;
	    $this->cascadePersistFalseCollection = new ArrayCollection();
	    $this->cascadePersistTrueCollection = new ArrayCollection();
	    $this->cascadeRefreshFalseCollection = new ArrayCollection();
	    $this->cascadeRefreshTrueCollection = new ArrayCollection();
	    $this->orphanRemovalTrueCollection = new ArrayCollection();
	    $this->orphanRemovalFalseCollection = new ArrayCollection();
        $this->reAddingToCollection = new ArrayCollection();
    }

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

	/**
	 * @return Collection|CascadePersistFalse[]
	 */
	public function getCascadePersistFalseCollection()
	{
		return $this->cascadePersistFalseCollection;
	}

	/**
	 * @return Collection|CascadePersistTrue[]
	 */
	public function getCascadePersistTrueCollection()
	{
		return $this->cascadePersistTrueCollection;
	}

    public function addOrphanRemovalTrue(OrphanRemovalTrue $object, bool $updateRelation = true): self
    {
        if ($this->orphanRemovalTrueCollection->contains($object)) {
            return $this;
        }
        $this->orphanRemovalTrueCollection->add($object);
        if ($updateRelation) {
            $object->setArticle($this, false);
        }
        return $this;
    }

    public function removeOrphanRemovalTrue(OrphanRemovalTrue $object, bool $updateRelation = true): self
    {
        $this->orphanRemovalTrueCollection->removeElement($object);
        //if ($updateRelation) {
        //    $comment->setArticle(null, false);
        //}
        return $this;
    }

	public function addOrphanRemovalFalse(OrphanRemovalFalse $object, bool $updateRelation = true): self
	{
		if ($this->orphanRemovalFalseCollection->contains($object)) {
			return $this;
		}
		$this->orphanRemovalFalseCollection->add($object);
		if ($updateRelation) {
			$object->setArticle($this, false);
		}
		return $this;
	}

	public function removeOrphanRemovalFalse(OrphanRemovalFalse $object, bool $updateRelation = true): self
	{
		$this->orphanRemovalFalseCollection->removeElement($object);
		//if ($updateRelation) {
		//    $comment->setArticle(null, false);
		//}
		return $this;
	}

    /**
     * @return Collection|OrphanRemovalTrue[]
     */
    public function getOrphanRemovalTrueCollection(): iterable
    {
        return $this->orphanRemovalTrueCollection;
    }

	/**
	 * @return Collection|OrphanRemovalTrue[]
	 */
	public function getOrphanRemovalFalseCollection(): iterable
	{
		return $this->orphanRemovalFalseCollection;
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

	public function addCascadePersistFalse(CascadePersistFalse $object, bool $updateRelation = true): self
	{
		if ($this->cascadePersistFalseCollection->contains($object)) {
			return $this;
		}
		$this->cascadePersistFalseCollection->add($object);
		if ($updateRelation) {
			$object->setArticle($this, false);
		}
		return $this;
	}

	public function addCascadePersistTrue(CascadePersistTrue $object, bool $updateRelation = true): self
	{
		if ($this->cascadePersistTrueCollection->contains($object)) {
			return $this;
		}
		$this->cascadePersistTrueCollection->add($object);
		if ($updateRelation) {
			$object->setArticle($this, false);
		}
		return $this;
	}

	public function addCascadeRefreshFalse(CascadeRefreshFalse $object, bool $updateRelation = true): self
	{
		if ($this->cascadeRefreshFalseCollection->contains($object)) {
			return $this;
		}
		$this->cascadeRefreshFalseCollection->add($object);
		if ($updateRelation) {
			$object->setArticle($this, false);
		}
		return $this;
	}

	public function addCascadeRefreshTrue(CascadeRefreshTrue $object, bool $updateRelation = true): self
	{
		if ($this->cascadeRefreshTrueCollection->contains($object)) {
			return $this;
		}
		$this->cascadeRefreshTrueCollection->add($object);
		if ($updateRelation) {
			$object->setArticle($this, false);
		}
		return $this;
	}

	/**
	 * @return Collection|CascadeRefreshFalse[]
	 */
	public function getCascadeRefreshFalseCollection()
	{
		return $this->cascadeRefreshFalseCollection;
	}

	/**
	 * @return Collection|CascadeRefreshTrue[]
	 */
	public function getCascadeRefreshTrueCollection()
	{
		return $this->cascadeRefreshTrueCollection;
	}

	//public function removeCascadeRefreshFalse(CascadeRefreshFalse $object): self
	//{
	//	$this->cascadeRefreshFalseCollection->removeElement($object);
	//	return $this;
	//}
//
//
	//public function removeCascadeRefreshTrue(CascadeRefreshTrue $object): self
	//{
	//	$this->cascadeRefreshTrueCollection->removeElement($object);
	//	return $this;
	//}

    /**
     * @return Collection|OrphanRemovalFalse[]
     */
    public function getReAddingToCollection()
    {
        return $this->reAddingToCollection;
    }

    /**
     * @param ReAddingToCollection $object
     * @return self
     */
    public function addReAddingToCollection(ReAddingToCollection $object): self
    {
        if ($this->reAddingToCollection->contains($object)) {
            return $this;
        }
        $this->reAddingToCollection->add($object);
        $object->setArticle($this);

        return $this;
    }
}
