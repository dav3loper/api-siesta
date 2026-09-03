<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903120000_create_film_festival_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table to store film festival editions';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('film_festival');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('edition_number', 'integer', ['notnull' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 256]);
        $table->addColumn('start_date', 'datetime', ['notnull' => true]);
        $table->addColumn('end_date', 'datetime', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('film_festival');
    }
}
