<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260427175021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_assignments
                            ADD COLUMN responded_at_new datetime NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE task_assignments
                            DROP COLUMN responded_at');
        $this->addSql('ALTER TABLE task_assignments
                            CHANGE responded_at_new responded_at datetime NULL default NULL');
        $this->addSql('CREATE UNIQUE INDEX task_assignment_user ON task_assignments (to_user_id, care_task_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_assignments CHANGE responded_at responded_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX task_assignment_user ON task_assignments');
    }
}
