<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625170248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', slug VARCHAR(64) NOT NULL, UNIQUE INDEX uq_categories_title (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item_ratings (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, user_id INT NOT NULL, value SMALLINT NOT NULL, INDEX IDX_C5EB219D126F525E (item_id), INDEX IDX_C5EB219DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE items (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(64) NOT NULL, description VARCHAR(255) NOT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', rating DOUBLE PRECISION DEFAULT NULL, image_filename VARCHAR(191) DEFAULT NULL, INDEX IDX_E11EE94D12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE items_tags (item_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_68503AA0126F525E (item_id), INDEX IDX_68503AA0BAD26311 (tag_id), PRIMARY KEY(item_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, user_id INT DEFAULT NULL, email VARCHAR(150) NOT NULL, nickname VARCHAR(50) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expiration_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', loan_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', temp_rating INT DEFAULT NULL, return_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_42C84955126F525E (item_id), INDEX IDX_42C84955A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', slug VARCHAR(64) NOT NULL, title VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, blocked TINYINT(1) DEFAULT 0 NOT NULL, nickname VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_ratings ADD CONSTRAINT FK_C5EB219D126F525E FOREIGN KEY (item_id) REFERENCES items (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_ratings ADD CONSTRAINT FK_C5EB219DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE items ADD CONSTRAINT FK_E11EE94D12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE items_tags ADD CONSTRAINT FK_68503AA0126F525E FOREIGN KEY (item_id) REFERENCES items (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE items_tags ADD CONSTRAINT FK_68503AA0BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955126F525E FOREIGN KEY (item_id) REFERENCES items (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE item_ratings DROP FOREIGN KEY FK_C5EB219D126F525E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item_ratings DROP FOREIGN KEY FK_C5EB219DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE items DROP FOREIGN KEY FK_E11EE94D12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE items_tags DROP FOREIGN KEY FK_68503AA0126F525E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE items_tags DROP FOREIGN KEY FK_68503AA0BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955126F525E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categories
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item_ratings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE items
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE items_tags
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tags
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
