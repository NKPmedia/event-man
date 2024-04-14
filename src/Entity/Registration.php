<?php

namespace App\Entity;

use App\Repository\RegistrationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
class Registration
{

    public const STATUS_PENDING = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_REJECTED = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegram_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?int $rank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegram_username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegram_firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegram_lastName = null;

    #[ORM\Column(nullable: true)]
    private ?int $telegram_chat_id = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelegramId(): ?string
    {
        return $this->telegram_id;
    }

    public function setTelegramId(?string $telegram_id): static
    {
        $this->telegram_id = $telegram_id;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getTelegramUsername(): ?string
    {
        return $this->telegram_username;
    }

    public function setTelegramUsername(?string $telegram_username): static
    {
        $this->telegram_username = $telegram_username;

        return $this;
    }

    public function getTelegramFirstName(): ?string
    {
        return $this->telegram_firstName;
    }

    public function setTelegramFirstName(?string $telegram_firstName): static
    {
        $this->telegram_firstName = $telegram_firstName;

        return $this;
    }

    public function getTelegramLastName(): ?string
    {
        return $this->telegram_lastName;
    }

    public function setTelegramLastName(?string $telegram_lastName): static
    {
        $this->telegram_lastName = $telegram_lastName;

        return $this;
    }

    public function getTelegramChatId(): ?int
    {
        return $this->telegram_chat_id;
    }

    public function setTelegramChatId(?int $telegram_chat_id): static
    {
        $this->telegram_chat_id = $telegram_chat_id;

        return $this;
    }

}
