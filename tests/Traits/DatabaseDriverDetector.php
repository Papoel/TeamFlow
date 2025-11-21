<?php

namespace App\Tests\Traits;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;

/**
 * Détecte le driver de base de données pour adapter les commandes SQL
 */
trait DatabaseDriverDetector
{
    /**
     * Détecte si la base de données est MySQL/MariaDB
     */
    protected function isMySQL(): bool
    {
        return $this->getPlatform() instanceof MySQLPlatform;
    }

    /**
     * Détecte si la base de données est PostgreSQL
     */
    protected function isPostgreSQL(): bool
    {
        return $this->getPlatform() instanceof PostgreSQLPlatform;
    }

    /**
     * Détecte si la base de données est SQLite
     */
    protected function isSQLite(): bool
    {
        return $this->getPlatform() instanceof SqlitePlatform;
    }

    /**
     * Récupère la plateforme de base de données
     */
    protected function getPlatform(): AbstractPlatform
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        return $entityManager->getConnection()->getDatabasePlatform();
    }
}
