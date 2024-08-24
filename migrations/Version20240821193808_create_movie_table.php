<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240821193808_create_movie_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table to store movies';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('movie');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('title', 'string', ['notnull' => true, 'length' => 512]);
        $table->addColumn('poster', 'string', ['notnull' => false, 'length' => 512]);
        $table->addColumn('trailer_id', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('duration', 'integer', ['notnull' => false]);
        $table->addColumn('summary', 'text', ['notnull' => false]);
        $table->addColumn('link', 'string', ['notnull' => false, 'length' => 512]);
        $table->addColumn('alias', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('comments', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->addColumn('film_festival_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('movie');
    }
}
