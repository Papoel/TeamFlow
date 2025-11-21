<?php

namespace App\Tests\Traits;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Trait pour nettoyer la base de données entre les tests
 * 
 * Compatible avec MySQL, PostgreSQL et SQLite
 * 
 * Usage:
 * - Ajouter `use RefreshDatabase;` dans votre classe de test
 * - La base sera automatiquement nettoyée avant chaque test
 */
trait RefreshDatabase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    /**
     * Nettoie toutes les tables de la base de données
     */
    private function refreshDatabase(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        // Récupère toutes les métadonnées des entités
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        // Désactive les contraintes de clés étrangères selon le SGBD
        if ($platform instanceof MySQLPlatform) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        } elseif ($platform instanceof PostgreSQLPlatform) {
            // PostgreSQL ne nécessite pas de désactivation avec TRUNCATE CASCADE
        } elseif ($platform instanceof SqlitePlatform) {
            $connection->executeStatement('PRAGMA foreign_keys = OFF');
        }

        // Vide chaque table selon le SGBD
        foreach ($metadata as $classMetadata) {
            /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
            $tableName = $classMetadata->getTableName();

            if ($platform instanceof MySQLPlatform) {
                $connection->executeStatement("TRUNCATE TABLE `{$tableName}`");
            } elseif ($platform instanceof PostgreSQLPlatform) {
                $connection->executeStatement("TRUNCATE TABLE \"{$tableName}\" RESTART IDENTITY CASCADE");
            } elseif ($platform instanceof SqlitePlatform) {
                $connection->executeStatement("DELETE FROM \"{$tableName}\"");
            }
        }

        // Réinitialise les séquences pour SQLite
        if ($platform instanceof SqlitePlatform) {
            $connection->executeStatement("DELETE FROM sqlite_sequence");
        }

        // Réactive les contraintes
        if ($platform instanceof MySQLPlatform) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        } elseif ($platform instanceof SqlitePlatform) {
            $connection->executeStatement('PRAGMA foreign_keys = ON');
        }

        // Nettoie le cache de l'EntityManager
        $entityManager->clear();
    }
}
