<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418115253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE wishlist (id SERIAL NOT NULL, owner_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9CE12A317E3C61F9 ON wishlist (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE wishlist_product (wishlist_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(wishlist_id, product_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C46D2D7FB8E54CD ON wishlist_product (wishlist_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C46D2D74584665A ON wishlist_product (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A317E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wishlist_product ADD CONSTRAINT FK_4C46D2D7FB8E54CD FOREIGN KEY (wishlist_id) REFERENCES wishlist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wishlist_product ADD CONSTRAINT FK_4C46D2D74584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wishlist DROP CONSTRAINT FK_9CE12A317E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wishlist_product DROP CONSTRAINT FK_4C46D2D7FB8E54CD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wishlist_product DROP CONSTRAINT FK_4C46D2D74584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE wishlist
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE wishlist_product
        SQL);
    }
}
