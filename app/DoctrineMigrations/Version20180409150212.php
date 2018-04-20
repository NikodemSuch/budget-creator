<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ondelete="cascade" to delete accounts, budgets and transactions when deleting user group.
 */
class Version20180409150212 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A47E3C61F9');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B7E3C61F9');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D136ABA6B8');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19B6B5FBA');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A47E3C61F9');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B7E3C61F9');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19B6B5FBA');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D136ABA6B8');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
    }
}
