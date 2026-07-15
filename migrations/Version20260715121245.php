<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20260716090000 extends AbstractMigration
{
    private const string TITLE = 'CONTRAT D\'ADHÉSION ZANDU RETRAITE';
    private const string BODY = <<<'TEXT'
Programme d'Épargne et de Protection Sociale des Commerçants des Marchés Domaniaux Connectés du Congo
...
[corps complet repris du prototype, placeholders inclus]
TEXT;

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO contract_template (id, title, body, is_active, updated_at) VALUES (?, ?, ?, 1, NOW())',
            [Uuid::v7()->toBinary(), self::TITLE, self::BODY]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM contract_template WHERE title = 'CONTRAT D\\'ADHÉSION ZANDU RETRAITE'");
    }
}
