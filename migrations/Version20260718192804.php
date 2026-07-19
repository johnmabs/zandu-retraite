<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718192804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Garantit qu'exactement un secteur "Autre" existe, dans tous les
        // environnements (dev/staging/prod) — pas seulement en dev via une
        // commande de seed qu'on pourrait oublier de jouer.
        $this->addSql(
            'INSERT INTO sector (id, name, code, is_other) VALUES (?, ?, ?, 1)',
            [Uuid::v7()->toBinary(), 'Autre', 'AUTRE']
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM sector WHERE code = 'AUTRE'");
    }
}
