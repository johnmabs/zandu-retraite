<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Génère un numéro de membre lisible (MR-0001) en s'appuyant sur
 * l'AUTO_INCREMENT natif de MySQL — atomique par construction, contrairement
 * à un SELECT MAX() + 1 qui serait sujet à une race condition sous
 * inscriptions concurrentes.
 */
final class MemberNumberGenerator
{
    public function __construct(private readonly Connection $connection) {}

    public function generate(): string
    {
        $this->connection->executeStatement('INSERT INTO member_number_sequence () VALUES ()');
        $id = (int) $this->connection->lastInsertId();

        return sprintf('MR-%04d', $id);
    }
}
