<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422104019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, descriptions VARCHAR(255) NOT NULL, qty INT DEFAULT 0 NOT NULL, unit VARCHAR(255) DEFAULT NULL, costprice NUMERIC(10, 2) DEFAULT 0 NOT NULL, sellprice NUMERIC(10, 2) DEFAULT 0 NOT NULL, saleprice NUMERIC(10, 2) DEFAULT 0 NOT NULL, productpicture VARCHAR(255) DEFAULT NULL, alertstocks INT DEFAULT 0 NOT NULL, criticalstocks INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, category_id INT NOT NULL, UNIQUE INDEX UNIQ_D34A04ADC96EAEB6 (descriptions), INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, salesamount NUMERIC(10, 2) DEFAULT 0 NOT NULL, salesdate DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, lastname VARCHAR(20) DEFAULT NULL, firstname VARCHAR(20) DEFAULT NULL, username VARCHAR(180) NOT NULL COLLATE `utf8mb4_bin`, password VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, mobile VARCHAR(15) DEFAULT NULL, isactivated INT DEFAULT 1 NOT NULL, isblocked INT DEFAULT 0 NOT NULL, secretkey LONGTEXT DEFAULT NULL, mailtoken INT DEFAULT 0 NOT NULL, qrcodeurl LONGTEXT DEFAULT NULL, userpic VARCHAR(255) DEFAULT \'pix.png\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, totp_secret VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE sale');
        $this->addSql('DROP TABLE `user`');
    }
}
