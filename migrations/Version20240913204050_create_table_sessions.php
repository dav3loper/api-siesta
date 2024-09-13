<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240913204050_create_table_sessions extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('sessions');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('movie_id', 'integer', ['notnull' => true]);
        $table->addColumn('location', 'string', ['notnull' => true, 'length' => 512]);
        $table->addColumn('init_date', 'datetime', ['notnull' => true]);
        $table->addColumn('end_date', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);

    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('sessions');

    }
}
