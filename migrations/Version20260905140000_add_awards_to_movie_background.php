<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905140000_add_awards_to_movie_background extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Awards of a movie and how many people voted its score, added to the agent background cache';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('movie_background');
        $table->addColumn('awards', 'text', ['notnull' => true]);
        $table->addColumn('audience_votes', 'integer', ['notnull' => true, 'default' => 0]);
    }

    /**
     * Rows cached before this migration get an empty string for awards, which is not the
     * empty JSON list the repository expects to decode.
     */
    public function postUp(Schema $schema): void
    {
        $this->connection->executeStatement("UPDATE movie_background SET awards='[]' WHERE awards=''");
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('movie_background');
        $table->dropColumn('awards');
        $table->dropColumn('audience_votes');
    }
}
