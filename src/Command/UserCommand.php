<?php

namespace App\Command;

use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user',
    description: '🚀 Gestion complète des utilisateurs (CRUD)',
)]
class UserCommand extends Command
{
    private const MENU_CREATE = '➕  Créer un utilisateur';
    private const MENU_LIST = '📋  Lister les utilisateurs';
    private const MENU_UPDATE = '✏️  Modifier un utilisateur';
    private const MENU_DELETE = '🗑️  Supprimer un utilisateur';
    private const MENU_EXIT = '🚪  Quitter';

    private const ROLE_MANAGER = 'ROLE_MANAGER';
    private const ROLE_INTERVENANT = 'ROLE_INTERVENANT';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->displayWelcomeBanner($io);

        while (true) {
            $action = $this->showMainMenu($io);

            if ($action === self::MENU_EXIT) {
                break;
            }

            match ($action) {
                self::MENU_CREATE => $this->createUser($io),
                self::MENU_LIST => $this->listUsers($io),
                self::MENU_UPDATE => $this->updateUser($io),
                self::MENU_DELETE => $this->deleteUser($io),
                default => null,
            };

            $io->newLine();
        }

        $io->success('👋 À bientôt !');
        return Command::SUCCESS;
    }

    private function displayWelcomeBanner(SymfonyStyle $io): void
    {
        $io->title('🎯 GESTION DES UTILISATEURS');
        $io->text([
            '<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>',
            '<fg=bright-white;options=bold>  TeamFlow - User Management System</>',
            '<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>',
        ]);
        $io->newLine();
    }

    private function showMainMenu(SymfonyStyle $io): string
    {
        $io->section('📌 Menu Principal');

        $question = new ChoiceQuestion(
            '<fg=yellow>Que souhaitez-vous faire ?</>',
            [
                self::MENU_CREATE,
                self::MENU_LIST,
                self::MENU_UPDATE,
                self::MENU_DELETE,
                self::MENU_EXIT,
            ],
            0
        );
        $question->setErrorMessage('Choix %s invalide.');

        return $io->askQuestion($question);
    }

    private function createUser(SymfonyStyle $io): void
    {
        $io->section('➕ Création d\'un nouvel utilisateur');

        try {
            $user = new User();

            // Demander le NNI
            $nni = $this->askForNni($io);
            if (!$nni) {
                return;
            }
            $user->setNni($nni);

            // Demander le mot de passe
            $password = $this->askForPassword($io);
            if (!$password) {
                return;
            }
            $user->setPassword($password);

            // Demander les rôles
            $roles = $this->askForRoles($io);
            $user->setRoles($roles);

            // Valider l'entité
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $io->error('❌ Erreurs de validation :');
                foreach ($errors as $error) {
                    $io->text('  • ' . $error->getMessage());
                }
                return;
            }

            // Sauvegarder (le mot de passe sera hashé par l'EventListener)
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success([
                '✅ Utilisateur créé avec succès !',
                sprintf('   NNI: %s', $user->getNni()),
                sprintf('   Rôles: %s', implode(', ', $user->getRoles())),
            ]);
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de la création : ' . $e->getMessage());
        }
    }

    private function listUsers(SymfonyStyle $io): void
    {
        $io->section('📋 Liste des utilisateurs');

        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $io->warning('⚠️  Aucun utilisateur trouvé.');
            return;
        }

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user->getId(),
                sprintf('<fg=cyan>%s</>', $user->getNni()),
                $this->formatRoles($user->getRoles()),
                '🔒 (hashé)',
            ];
        }

        $io->table(
            ['ID', 'NNI', 'Rôles', 'Mot de passe'],
            $rows
        );

        $io->info(sprintf('📊 Total : %d utilisateur(s)', count($users)));
    }

    private function updateUser(SymfonyStyle $io): void
    {
        $io->section('✏️  Modification d\'un utilisateur');

        $users = $this->userRepository->findAll();
        if (empty($users)) {
            $io->warning('⚠️  Aucun utilisateur à modifier.');
            return;
        }

        // Sélectionner l'utilisateur
        $userChoices = [];
        foreach ($users as $user) {
            $userChoices[$user->getId()] = sprintf(
                '%s (ID: %d) - %s',
                $user->getNni(),
                $user->getId(),
                implode(', ', $user->getRoles())
            );
        }

        $question = new ChoiceQuestion(
            '<fg=yellow>Sélectionnez l\'utilisateur à modifier :</>',
            $userChoices
        );
        $selectedId = array_search($io->askQuestion($question), $userChoices);

        $user = $this->userRepository->find($selectedId);
        if (!$user) {
            $io->error('❌ Utilisateur introuvable.');
            return;
        }

        $io->note(sprintf('Modification de l\'utilisateur : %s', $user->getNni()));

        // Demander quoi modifier
        $fieldQuestion = new ChoiceQuestion(
            '<fg=yellow>Que souhaitez-vous modifier ?</>',
            ['NNI', 'Mot de passe', 'Rôles', 'Annuler']
        );
        $field = $io->askQuestion($fieldQuestion);

        try {
            match ($field) {
                'NNI' => $this->updateNni($io, $user),
                'Mot de passe' => $this->updatePassword($io, $user),
                'Rôles' => $this->updateRoles($io, $user),
                'Annuler' => null,
            };

            if ($field !== 'Annuler') {
                $errors = $this->validator->validate($user);
                if (count($errors) > 0) {
                    $io->error('❌ Erreurs de validation :');
                    foreach ($errors as $error) {
                        $io->text('  • ' . $error->getMessage());
                    }
                    return;
                }

                $this->entityManager->flush();
                $io->success('✅ Utilisateur mis à jour avec succès !');
            }
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    private function deleteUser(SymfonyStyle $io): void
    {
        $io->section('🗑️  Suppression d\'un utilisateur');

        $users = $this->userRepository->findAll();
        if (empty($users)) {
            $io->warning('⚠️  Aucun utilisateur à supprimer.');
            return;
        }

        // Sélectionner l'utilisateur
        $userChoices = [];
        foreach ($users as $user) {
            $userChoices[$user->getId()] = sprintf(
                '%s (ID: %d)',
                $user->getNni(),
                $user->getId()
            );
        }

        $question = new ChoiceQuestion(
            '<fg=yellow>Sélectionnez l\'utilisateur à supprimer :</>',
            $userChoices
        );
        $selectedId = array_search($io->askQuestion($question), $userChoices);

        $user = $this->userRepository->find($selectedId);
        if (!$user) {
            $io->error('❌ Utilisateur introuvable.');
            return;
        }

        // Demander confirmation
        $confirmQuestion = new ConfirmationQuestion(
            sprintf(
                '<fg=red>⚠️  Êtes-vous sûr de vouloir supprimer l\'utilisateur "%s" ? (y/N)</>',
                $user->getNni()
            ),
            false
        );

        if ($io->askQuestion($confirmQuestion)) {
            try {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
                $io->success('✅ Utilisateur supprimé avec succès !');
            } catch (\Exception $e) {
                $io->error('❌ Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $io->info('❌ Suppression annulée.');
        }
    }

    // ============= Méthodes utilitaires (Single Responsibility) =============

    private function askForNni(SymfonyStyle $io): ?string
    {
        $nni = $io->ask(
            '<fg=yellow>NNI (format: A12345 - 1 lettre + 5 chiffres)</>',
            null,
            function ($answer) {
                if (!$answer) {
                    throw new \RuntimeException('Le NNI est obligatoire.');
                }
                if (!preg_match('/^[A-Za-z]\d{5}$/', $answer)) {
                    throw new \RuntimeException(
                        'Format invalide. Le NNI doit contenir 1 lettre suivie de 5 chiffres (ex: A12345)'
                    );
                }
                return strtoupper($answer[0]) . substr($answer, 1);
            }
        );

        // Vérifier l'unicité
        if ($this->userRepository->findOneBy(['nni' => $nni])) {
            $io->error('❌ Ce NNI est déjà utilisé.');
            return null;
        }

        return $nni;
    }

    private function askForPassword(SymfonyStyle $io): ?string
    {
        return $io->askHidden(
            '<fg=yellow>Mot de passe (min 4 caractères, avec maj, min et chiffre)</>',
            function ($answer) {
                if (!$answer) {
                    throw new \RuntimeException('Le mot de passe est obligatoire.');
                }
                if (strlen($answer) < 4) {
                    throw new \RuntimeException('Le mot de passe doit contenir au moins 4 caractères.');
                }
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{4,}$/', $answer)) {
                    throw new \RuntimeException(
                        'Le mot de passe doit contenir au moins une minuscule, une majuscule et un chiffre.'
                    );
                }
                return $answer;
            }
        );
    }

    private function askForRoles(SymfonyStyle $io): array
    {
        $question = new ChoiceQuestion(
            '<fg=yellow>Sélectionnez le(s) rôle(s) (séparés par des virgules si plusieurs)</>',
            [self::ROLE_INTERVENANT, self::ROLE_MANAGER],
            0
        );
        $question->setMultiselect(true);

        return $io->askQuestion($question);
    }

    private function updateNni(SymfonyStyle $io, User $user): void
    {
        $nni = $this->askForNni($io);
        if ($nni) {
            $user->setNni($nni);
        }
    }

    private function updatePassword(SymfonyStyle $io, User $user): void
    {
        $password = $this->askForPassword($io);
        if ($password) {
            $user->setPassword($password);
        }
    }

    private function updateRoles(SymfonyStyle $io, User $user): void
    {
        $roles = $this->askForRoles($io);
        $user->setRoles($roles);
    }

    private function formatRoles(array $roles): string
    {
        $formatted = [];
        foreach ($roles as $role) {
            $icon = $role === self::ROLE_MANAGER ? '👔' : '🔧';
            $color = $role === self::ROLE_MANAGER ? 'green' : 'blue';
            $formatted[] = sprintf('<fg=%s>%s %s</>', $color, $icon, $role);
        }
        return implode(', ', $formatted);
    }
}
