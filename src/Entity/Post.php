<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Tag;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Category;
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping\Id;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Column(type: Types::STRING)]
    #[NotBlank()]
    private string $title;

    #[Column(type: Types::TEXT)]
    #[NotBlank()]
    private string $content;

    #[Column(type: Types::STRING)]
    private string $image;

    #[Image(maxSize: '1M', maxRatio: 4/3, minRatio: 4/3)]
    #[NotNull(groups: ['create'])]
    private ?UploadedFile $imageFile = null;

    #[Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $publishedAt;

    #[ManyToOne(targetEntity: Category::class, inversedBy: 'posts')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull()]
    private Category $category;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ManyToMany(targetEntity: Tag::class)]
    #[JoinTable(name: 'post_tags')]
    #[Count(min: 1)]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }
 
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }
    
    public function getImage()
    {
        return $this->image;
    }
    
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function getPublishedAt(): DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(DateTimeImmutable $publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setTags(Collection $tags)
    {
        $this->tags = $tags;

        return $this;
    }
}