<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240913204048_add_section_to_movie extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('movie');
        $table->addColumn('section', 'string', ['notnull' => false, 'length' => 512]);

    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('movie');
        $table->dropColumn('section');

    }
}
