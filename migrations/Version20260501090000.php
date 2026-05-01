<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cascade notification deletion when linked care tasks or propagation actions are deleted.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D35765E605');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3CDD79FB3');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D35765E605 FOREIGN KEY (propagation_action_id) REFERENCES propagation_actions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3CDD79FB3 FOREIGN KEY (care_task_id) REFERENCES care_task (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D35765E605');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3CDD79FB3');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D35765E605 FOREIGN KEY (propagation_action_id) REFERENCES propagation_actions (id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3CDD79FB3 FOREIGN KEY (care_task_id) REFERENCES care_task (id)');
    }
}
