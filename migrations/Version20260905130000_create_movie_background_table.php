<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905130000_create_movie_background_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cache of the external information about a movie used by the agent, to avoid asking TMDB twice';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('movie_background');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('searched_title', 'string', ['length' => 512, 'notnull' => true]);
        $table->addColumn('title', 'string', ['length' => 512, 'notnull' => true]);
        $table->addColumn('year', 'integer', ['notnull' => false]);
        $table->addColumn('director', 'string', ['length' => 256, 'notnull' => false]);
        $table->addColumn('genres', 'text', ['notnull' => true]);
        $table->addColumn('main_cast', 'text', ['notnull' => true]);
        $table->addColumn('other_movies_by_director', 'text', ['notnull' => true]);
        $table->addColumn('audience_score', 'float', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['searched_title']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('movie_background');
    }
}
