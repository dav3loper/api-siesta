<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908120000_add_media_locks_to_movie extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Marks the poster or the trailer of a movie as corrected by a person, so the importer stops overwriting it';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('movie');
        $table->addColumn('poster_locked', 'boolean', ['notnull' => true, 'default' => false]);
        $table->addColumn('trailer_locked', 'boolean', ['notnull' => true, 'default' => false]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('movie');
        $table->dropColumn('poster_locked');
        $table->dropColumn('trailer_locked');
    }
}
