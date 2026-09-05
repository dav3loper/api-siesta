<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905120000_create_agent_interaction_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table to audit every conversation turn handled by the recommendation agent';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('agent_interaction');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('conversation_id', 'string', ['length' => 36, 'notnull' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('group_id', 'integer', ['notnull' => false]);
        $table->addColumn('movie_title', 'string', ['length' => 512, 'notnull' => false]);
        $table->addColumn('movie_year', 'integer', ['notnull' => false]);
        $table->addColumn('user_message', 'text', ['notnull' => true]);
        $table->addColumn('agent_response', 'text', ['notnull' => false]);
        $table->addColumn('tool_calls', 'text', ['notnull' => false]);
        $table->addColumn('unknown_titles', 'text', ['notnull' => false]);
        $table->addColumn('status', 'string', ['length' => 32, 'notnull' => true]);
        $table->addColumn('error', 'text', ['notnull' => false]);
        $table->addColumn('model', 'string', ['length' => 64, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id', 'created_at']);
        $table->addIndex(['conversation_id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('agent_interaction');
    }
}
