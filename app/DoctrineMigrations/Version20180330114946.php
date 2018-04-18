<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Invitation now keeps it's notification ID
 */
class Version20180330114946 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_invitation ADD notification_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE group_invitation ADD CONSTRAINT FK_26D00010EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_26D00010EF1A9D84 ON group_invitation (notification_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_invitation DROP FOREIGN KEY FK_26D00010EF1A9D84');
        $this->addSql('DROP INDEX UNIQ_26D00010EF1A9D84 ON group_invitation');
        $this->addSql('ALTER TABLE group_invitation DROP notification_id');
    }
}
