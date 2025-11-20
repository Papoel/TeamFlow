<?php

namespace App\Entity\User;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\User\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NNI', fields: ['nni'])]
#[UniqueEntity(
    fields: ['nni'],
    message: 'Ce NNI est déjà utilisé'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 8, unique: true)]
    #[Assert\NotBlank(message: 'Le NNI est obligatoire')]
    #[Assert\Length(
        exactly: 6,
        exactMessage: 'Le NNI doit contenir exactement {{ limit }} caractères',
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]\d{5}$/',
        message: 'Le NNI doit commencer par une lettre majuscule suivie de 5 chiffres (ex: A12345)'
    )]
    private ?string $nni = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    #[Assert\NotNull(message: 'Les rôles ne peuvent pas être null')]
    #[Assert\All([
        new Assert\Choice(
            choices: ['ROLE_MANAGER', 'ROLE_INTERVENANT'],
            message: 'Le rôle "{{ value }}" n\'est pas valide'
        )
    ])]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank(message: 'Veuillez saisir un mot de passe.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/',
        message: 'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule et un chiffre.'
    )]
    #[Assert\Length(
        min: 4,
        max: 255,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le mot de passe doit contenir au maximum {{ limit }} caractères.'
    )]
    private ?string $password = null;

    public function __construct()
    {
        // Get ROLE_INTERVENANT by default
        $this->roles = ['ROLE_INTERVENANT'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNni(): ?string
    {
        return $this->nni;
    }

    public function setNni(string $nni): static
    {
        $this->nni = $this->normalizeNni($nni);

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->nni;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    // *************** Custom methods ***************
    /**
     * Normalise le NNI en mettant la première lettre en majuscule
     */
    private function normalizeNni(string $nni): string
    {
        if (strlen($nni) === 0) {
            return $nni;
        }

        // Met le premier caractère en majuscule + garde le reste tel quel
        return strtoupper($nni[0]) . substr($nni, 1);
    }
}
