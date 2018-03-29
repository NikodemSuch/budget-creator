<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Group Invitation Table
 */
class Version20180329232908 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE group_invitation (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, user_group_id INT DEFAULT NULL, created_on DATETIME NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_26D00010A76ED395 (user_id), INDEX IDX_26D000101ED93D47 (user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE group_invitation ADD CONSTRAINT FK_26D00010A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_invitation ADD CONSTRAINT FK_26D000101ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE group_invitation');
    }
}
