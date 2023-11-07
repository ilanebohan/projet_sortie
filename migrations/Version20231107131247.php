<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107131247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription_participants (inscription_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_F74DD3DB5DAC5993 (inscription_id), INDEX IDX_F74DD3DBA76ED395 (user_id), PRIMARY KEY(inscription_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inscription_participants ADD CONSTRAINT FK_F74DD3DB5DAC5993 FOREIGN KEY (inscription_id) REFERENCES inscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_participants ADD CONSTRAINT FK_F74DD3DBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6838709D5');
        $this->addSql('DROP INDEX IDX_5E90F6D6838709D5 ON inscription');
        $this->addSql('ALTER TABLE inscription DROP participants_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_participants DROP FOREIGN KEY FK_F74DD3DB5DAC5993');
        $this->addSql('ALTER TABLE inscription_participants DROP FOREIGN KEY FK_F74DD3DBA76ED395');
        $this->addSql('DROP TABLE inscription_participants');
        $this->addSql('ALTER TABLE inscription ADD participants_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6838709D5 FOREIGN KEY (participants_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5E90F6D6838709D5 ON inscription (participants_id)');
    }
}
