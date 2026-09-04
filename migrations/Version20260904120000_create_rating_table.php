<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260904120000_create_rating_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table to store ratings from user for watched movies';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('rating');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('movie_id', 'integer', ['notnull' => true]);
        $table->addColumn('score', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id', 'movie_id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('rating');
    }
}
