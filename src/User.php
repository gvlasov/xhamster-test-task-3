<?php

namespace Gvlasov\XhamsterTestTask3;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gvlasov\XhamsterTestTask3\Repository\UserRepository;

#[Entity(repositoryClass: UserRepository::class), Table(name: 'users')]
#[HasLifecycleCallbacks]
#[SoftDeleteable(fieldName: 'deleted', timeAware: false, hardDelete: false)]
class User
{

    #[Id, GeneratedValue, Column(type: 'integer')]
    private int $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $email;

    #[Column(type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTime $created;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTime $deleted = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getDeleted(): ?DateTime
    {
        return $this->deleted;
    }

    public function setCreated(DateTime $time): void
    {
        $this->created = $time;
    }

    public function setDeleted(?DateTime $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getEmailDomain(): string
    {
        return explode('@', $this->email)[1];
    }

    public function hasEmail(): bool
    {
        return isset($this->email);
    }

    public function hasName(): bool
    {
        return isset($this->name);
    }

    /**
     * Registers User in a Doctrine EntityManager, adding validation rules for User
     */
    public static function registerSubscribers(
        EntityManager       $em,
        UserValidator       $validator,
        UserModificationLog $log
    ): void
    {
        if (!$em->getFilters()->has('soft-deletable')) {
            throw new \Error('Soft-deletable filter is not enabled');
        }
        $em->getFilters()->enable('soft-deletable');
        if (!($em->getFilters()->getFilter('soft-deletable') instanceof SoftDeleteableFilter)) {
            throw new \Error('Wrong filter is specified as \'soft-deletable\'');
        }
        $em->getEventManager()->addEventSubscriber(
            new UserValidationSubscriber($validator)
        );
        $em->getEventManager()->addEventSubscriber(
            new UserModificationLogSubscriber($log)
        );
        $em->getEventManager()->addEventSubscriber(new SoftDeleteableListener());
    }

}