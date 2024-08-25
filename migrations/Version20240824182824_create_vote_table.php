<?php

declare(strict_types=1);

namespace Siesta\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240824182824_create_vote_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table to store votes from user';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('vote');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('movie_id', 'integer', ['notnull' => true]);
        $table->addColumn('score', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['movie_id']);

    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('vote');

    }
}
