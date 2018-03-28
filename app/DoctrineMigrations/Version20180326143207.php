<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Initial migration
 */
class Version20180326143207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(200) NOT NULL, currency VARCHAR(20) NOT NULL, INDEX IDX_7D3656A47E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(200) NOT NULL, currency VARCHAR(20) NOT NULL, INDEX IDX_73F2F77B7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, name VARCHAR(200) NOT NULL, INDEX IDX_64C19C1FE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_group (id INT AUTO_INCREMENT NOT NULL, default_category_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_85F30B8CC6B58E54 (default_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, recipient_id INT DEFAULT NULL, content VARCHAR(200) NOT NULL, created_on DATETIME NOT NULL, INDEX IDX_BF5476CAE92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, creator_id INT DEFAULT NULL, account_id INT DEFAULT NULL, budget_id INT DEFAULT NULL, category_id INT DEFAULT NULL, transfer_slave_id INT DEFAULT NULL, title VARCHAR(200) DEFAULT NULL, amount BIGINT NOT NULL COMMENT \'(DC2Type:money)\', date_time DATETIME NOT NULL, is_transfer_half TINYINT(1) NOT NULL, INDEX IDX_723705D161220EA6 (creator_id), INDEX IDX_723705D19B6B5FBA (account_id), INDEX IDX_723705D136ABA6B8 (budget_id), INDEX IDX_723705D112469DE2 (category_id), UNIQUE INDEX UNIQ_723705D1CA989062 (transfer_slave_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, transaction_master_id INT DEFAULT NULL, transfer_type VARCHAR(256) COMMENT "transferType" NOT NULL COMMENT \'(DC2Type:transferType)\', UNIQUE INDEX UNIQ_4034A3C050944052 (transaction_master_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(60) NOT NULL, username VARCHAR(60) NOT NULL, password VARCHAR(60) NOT NULL, is_active TINYINT(1) NOT NULL, role VARCHAR(256) COMMENT "userRole" NOT NULL COMMENT \'(DC2Type:userRole)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_membership (user_id INT NOT NULL, user_group_id INT NOT NULL, INDEX IDX_21981469A76ED395 (user_id), INDEX IDX_219814691ED93D47 (user_group_id), PRIMARY KEY(user_id, user_group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unread_notifications (user_id INT NOT NULL, notification_id INT NOT NULL, INDEX IDX_1E1B6DE5A76ED395 (user_id), INDEX IDX_1E1B6DE5EF1A9D84 (notification_id), PRIMARY KEY(user_id, notification_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(200) NOT NULL, is_default_group TINYINT(1) NOT NULL, INDEX IDX_8F02BF9D7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1FE54D947 FOREIGN KEY (group_id) REFERENCES category_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_group ADD CONSTRAINT FK_85F30B8CC6B58E54 FOREIGN KEY (default_category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE92F8F78 FOREIGN KEY (recipient_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1CA989062 FOREIGN KEY (transfer_slave_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C050944052 FOREIGN KEY (transaction_master_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE user_membership ADD CONSTRAINT FK_21981469A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_membership ADD CONSTRAINT FK_219814691ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unread_notifications ADD CONSTRAINT FK_1E1B6DE5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unread_notifications ADD CONSTRAINT FK_1E1B6DE5EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19B6B5FBA');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D136ABA6B8');
        $this->addSql('ALTER TABLE category_group DROP FOREIGN KEY FK_85F30B8CC6B58E54');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D112469DE2');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1FE54D947');
        $this->addSql('ALTER TABLE unread_notifications DROP FOREIGN KEY FK_1E1B6DE5EF1A9D84');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1CA989062');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C050944052');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D161220EA6');
        $this->addSql('ALTER TABLE user_membership DROP FOREIGN KEY FK_21981469A76ED395');
        $this->addSql('ALTER TABLE unread_notifications DROP FOREIGN KEY FK_1E1B6DE5A76ED395');
        $this->addSql('ALTER TABLE user_group DROP FOREIGN KEY FK_8F02BF9D7E3C61F9');
        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A47E3C61F9');
        $this->addSql('ALTER TABLE budget DROP FOREIGN KEY FK_73F2F77B7E3C61F9');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAE92F8F78');
        $this->addSql('ALTER TABLE user_membership DROP FOREIGN KEY FK_219814691ED93D47');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_group');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_membership');
        $this->addSql('DROP TABLE unread_notifications');
        $this->addSql('DROP TABLE user_group');
    }
}
