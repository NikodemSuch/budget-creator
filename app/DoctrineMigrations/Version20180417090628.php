<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change DateTime columns to DateTimeImmutable
 */
class Version20180417090628 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_invitation CHANGE created_on created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE notification CHANGE created_on created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE transaction ADD created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP date_time');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_invitation CHANGE created_on created_on DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification CHANGE created_on created_on DATETIME NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD date_time DATETIME NOT NULL, DROP created_on');
    }
}
