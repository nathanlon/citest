<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230905022714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bank_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bank_account (id INT NOT NULL, customer_id INT NOT NULL, account_number VARCHAR(11) NOT NULL, account_type VARCHAR(15) NOT NULL, account_name VARCHAR(100) NOT NULL, currency VARCHAR(3) NOT NULL, is_preferred BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_53A23E0A9395C3F3 ON bank_account (customer_id)');
        $this->addSql('CREATE TABLE customer (id INT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, ssn VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE bank_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('ALTER TABLE bank_account DROP CONSTRAINT FK_53A23E0A9395C3F3');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('DROP TABLE customer');
    }
}
