<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903120100_seed_edition_9_film_festival extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed the 58th and 59th Sitges Film Festival editions (ids 8 and 9, already used as movie.film_festival_id)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO film_festival (id, edition_number, name, start_date, end_date, created_at, updated_at)
            VALUES (9, 59, 'Sitges Film Festival', '2026-10-08 00:00:00', '2026-10-18 00:00:00', NOW(), NOW())");
        $this->addSql("INSERT INTO film_festival (id, edition_number, name, start_date, end_date, created_at, updated_at)
            VALUES (8, 58, 'Sitges Film Festival', '2025-10-09 00:00:00', '2025-10-19 00:00:00', NOW(), NOW())");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM film_festival WHERE id IN (8, 9)');
    }
}
