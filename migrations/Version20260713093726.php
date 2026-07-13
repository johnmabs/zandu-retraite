<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713093726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_user (id BINARY(16) NOT NULL, full_name VARCHAR(100) NOT NULL, login VARCHAR(50) NOT NULL, email VARCHAR(180) DEFAULT NULL, pin VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL, permissions JSON NOT NULL, status VARCHAR(20) NOT NULL, last_login_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_AD8A54A9AA08CB10 (login), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE audit_log (id BINARY(16) NOT NULL, event_type VARCHAR(40) NOT NULL, description LONGTEXT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, context JSON DEFAULT NULL, created_at DATETIME NOT NULL, actor_admin_id BINARY(16) DEFAULT NULL, actor_member_id BINARY(16) DEFAULT NULL, INDEX IDX_F6E1C0F5C96EFF06 (actor_admin_id), INDEX IDX_F6E1C0F581EE23D2 (actor_member_id), INDEX IDX_F6E1C0F593151B828B8E8428 (event_type, created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE change_request (id BINARY(16) NOT NULL, type VARCHAR(30) NOT NULL, requested_value VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, review_note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, reviewed_at DATETIME DEFAULT NULL, member_id BINARY(16) NOT NULL, reviewed_by_id BINARY(16) DEFAULT NULL, INDEX IDX_CB902D367597D3FE (member_id), INDEX IDX_CB902D36FC6B21F1 (reviewed_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chat_message (id BINARY(16) NOT NULL, sender_type VARCHAR(10) NOT NULL, content LONGTEXT NOT NULL, sent_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, member_id BINARY(16) NOT NULL, sender_admin_id BINARY(16) DEFAULT NULL, INDEX IDX_FAB3FC167597D3FE (member_id), INDEX IDX_FAB3FC1668106FEE (sender_admin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contract_template (id BINARY(16) NOT NULL, title VARCHAR(200) NOT NULL, body LONGTEXT NOT NULL, is_active TINYINT NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE issued_contract (id BINARY(16) NOT NULL, resolved_body LONGTEXT NOT NULL, pdf_path VARCHAR(255) DEFAULT NULL, issued_at DATETIME NOT NULL, signed_at DATETIME DEFAULT NULL, member_id BINARY(16) NOT NULL, template_id BINARY(16) NOT NULL, INDEX IDX_168FC6BC7597D3FE (member_id), INDEX IDX_168FC6BC5DA0FB8 (template_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `member` (id BINARY(16) NOT NULL, member_number VARCHAR(20) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, gender VARCHAR(10) DEFAULT NULL, birth_date DATE DEFAULT NULL, phone VARCHAR(20) NOT NULL, whatsapp_phone VARCHAR(20) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, id_document_number VARCHAR(50) DEFAULT NULL, profession VARCHAR(100) DEFAULT NULL, custom_sector_label VARCHAR(150) DEFAULT NULL, pin VARCHAR(255) NOT NULL, photo_path VARCHAR(255) DEFAULT NULL, daily_payment_amount NUMERIC(12, 2) NOT NULL, salary_category VARCHAR(255) NOT NULL, engagement_duration INT DEFAULT NULL, savings_goal VARCHAR(255) DEFAULT NULL, goal_details VARCHAR(150) DEFAULT NULL, pension_rate NUMERIC(5, 2) DEFAULT NULL, management_fee_rate NUMERIC(5, 2) DEFAULT NULL, cnss_rate NUMERIC(5, 2) DEFAULT NULL, registration_fee_amount NUMERIC(12, 2) DEFAULT NULL, status VARCHAR(20) NOT NULL, last_login_at DATETIME DEFAULT NULL, registered_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, activity_location_department VARCHAR(100) DEFAULT NULL, activity_location_commune VARCHAR(100) DEFAULT NULL, activity_location_quarter VARCHAR(100) DEFAULT NULL, activity_location_market_zone VARCHAR(150) DEFAULT NULL, activity_location_market_spot VARCHAR(150) DEFAULT NULL, home_address_department VARCHAR(100) DEFAULT NULL, home_address_commune VARCHAR(100) DEFAULT NULL, home_address_quarter VARCHAR(100) DEFAULT NULL, home_address_street VARCHAR(150) DEFAULT NULL, home_address_number VARCHAR(20) DEFAULT NULL, home_address_locality VARCHAR(150) DEFAULT NULL, beneficiary_name VARCHAR(150) DEFAULT NULL, beneficiary_phone VARCHAR(20) DEFAULT NULL, sector_id BINARY(16) NOT NULL, sub_sector_id BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_70E4FA78B2469D67 (member_number), UNIQUE INDEX UNIQ_70E4FA78444F97DD (phone), INDEX IDX_70E4FA78DE95C867 (sector_id), INDEX IDX_70E4FA78CC988ED9 (sub_sector_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id BINARY(16) NOT NULL, type VARCHAR(40) NOT NULL, message LONGTEXT NOT NULL, context JSON DEFAULT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, related_member_id BINARY(16) DEFAULT NULL, INDEX IDX_BF5476CADD568FE5 (related_member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payment (id BINARY(16) NOT NULL, amount NUMERIC(12, 2) NOT NULL, payment_date DATE NOT NULL, payment_method VARCHAR(20) NOT NULL, source VARCHAR(20) NOT NULL, confirmation_method VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, sender_phone_number VARCHAR(20) DEFAULT NULL, external_reference VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, member_id BINARY(16) NOT NULL, recorded_by_id BINARY(16) DEFAULT NULL, INDEX IDX_6D28840D7597D3FE (member_id), INDEX IDX_6D28840DD05A957B (recorded_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payslip (id BINARY(16) NOT NULL, payslip_number VARCHAR(40) NOT NULL, period_start DATE NOT NULL, period_end DATE NOT NULL, payments_count INT NOT NULL, gross_amount NUMERIC(12, 2) NOT NULL, pension_share_amount NUMERIC(12, 2) NOT NULL, management_fee_amount NUMERIC(12, 2) NOT NULL, cnss_contribution_amount NUMERIC(12, 2) NOT NULL, net_amount NUMERIC(12, 2) NOT NULL, sent_via VARCHAR(20) DEFAULT NULL, pdf_path VARCHAR(255) DEFAULT NULL, issued_at DATETIME NOT NULL, member_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_9A13CDF0531623F6 (payslip_number), INDEX IDX_9A13CDF07597D3FE (member_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sector (id BINARY(16) NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_4BA3D9E85E237E06 (name), UNIQUE INDEX UNIQ_4BA3D9E877153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE setting (id BINARY(16) NOT NULL, default_pension_rate NUMERIC(5, 2) NOT NULL, default_management_fee_rate NUMERIC(5, 2) NOT NULL, default_cnss_rate NUMERIC(5, 2) NOT NULL, registration_fee_amount NUMERIC(12, 2) NOT NULL, mtn_momo_number VARCHAR(20) DEFAULT NULL, airtel_money_number VARCHAR(20) DEFAULT NULL, bank_iban VARCHAR(30) DEFAULT NULL, bank_name VARCHAR(100) DEFAULT NULL, mtn_api_environment VARCHAR(20) NOT NULL, airtel_api_environment VARCHAR(20) NOT NULL, cnss_api_environment VARCHAR(20) NOT NULL, salary_category_thresholds JSON NOT NULL, notify_admin_by_email TINYINT NOT NULL, notify_admin_by_sms TINYINT NOT NULL, notify_admin_by_whatsapp TINYINT NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sub_sector (id BINARY(16) NOT NULL, name VARCHAR(100) NOT NULL, sector_id BINARY(16) NOT NULL, INDEX IDX_F9F296A4DE95C867 (sector_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5C96EFF06 FOREIGN KEY (actor_admin_id) REFERENCES admin_user (id)');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F581EE23D2 FOREIGN KEY (actor_member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE change_request ADD CONSTRAINT FK_CB902D367597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE change_request ADD CONSTRAINT FK_CB902D36FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES admin_user (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC167597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC1668106FEE FOREIGN KEY (sender_admin_id) REFERENCES admin_user (id)');
        $this->addSql('ALTER TABLE issued_contract ADD CONSTRAINT FK_168FC6BC7597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE issued_contract ADD CONSTRAINT FK_168FC6BC5DA0FB8 FOREIGN KEY (template_id) REFERENCES contract_template (id)');
        $this->addSql('ALTER TABLE `member` ADD CONSTRAINT FK_70E4FA78DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
        $this->addSql('ALTER TABLE `member` ADD CONSTRAINT FK_70E4FA78CC988ED9 FOREIGN KEY (sub_sector_id) REFERENCES sub_sector (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CADD568FE5 FOREIGN KEY (related_member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D7597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DD05A957B FOREIGN KEY (recorded_by_id) REFERENCES admin_user (id)');
        $this->addSql('ALTER TABLE payslip ADD CONSTRAINT FK_9A13CDF07597D3FE FOREIGN KEY (member_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE sub_sector ADD CONSTRAINT FK_F9F296A4DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F5C96EFF06');
        $this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F581EE23D2');
        $this->addSql('ALTER TABLE change_request DROP FOREIGN KEY FK_CB902D367597D3FE');
        $this->addSql('ALTER TABLE change_request DROP FOREIGN KEY FK_CB902D36FC6B21F1');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC167597D3FE');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC1668106FEE');
        $this->addSql('ALTER TABLE issued_contract DROP FOREIGN KEY FK_168FC6BC7597D3FE');
        $this->addSql('ALTER TABLE issued_contract DROP FOREIGN KEY FK_168FC6BC5DA0FB8');
        $this->addSql('ALTER TABLE `member` DROP FOREIGN KEY FK_70E4FA78DE95C867');
        $this->addSql('ALTER TABLE `member` DROP FOREIGN KEY FK_70E4FA78CC988ED9');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CADD568FE5');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D7597D3FE');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DD05A957B');
        $this->addSql('ALTER TABLE payslip DROP FOREIGN KEY FK_9A13CDF07597D3FE');
        $this->addSql('ALTER TABLE sub_sector DROP FOREIGN KEY FK_F9F296A4DE95C867');
        $this->addSql('DROP TABLE admin_user');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE change_request');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE contract_template');
        $this->addSql('DROP TABLE issued_contract');
        $this->addSql('DROP TABLE `member`');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payslip');
        $this->addSql('DROP TABLE sector');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE sub_sector');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
