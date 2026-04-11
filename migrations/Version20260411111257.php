<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260411111257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE care_history (id INT AUTO_INCREMENT NOT NULL, care_type VARCHAR(255) NOT NULL, performed_at DATETIME NOT NULL, water_amount_ml INT DEFAULT NULL, fertilizer_amount_ml INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, plant_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_67DBEE7E1D935652 (plant_id), INDEX IDX_67DBEE7EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE care_task (id INT AUTO_INCREMENT NOT NULL, task_type VARCHAR(255) NOT NULL, due_date DATETIME NOT NULL, created_at DATETIME NOT NULL, plant_id INT NOT NULL, INDEX IDX_75110A221D935652 (plant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE locations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, light_condition VARCHAR(255) NOT NULL, temperature_level VARCHAR(255) NOT NULL, humidity_level VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_17E64ABAA76ED395 (user_id), UNIQUE INDEX user_location_name (user_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_read TINYINT NOT NULL, is_active TINYINT NOT NULL, user_id INT NOT NULL, propagation_action_id INT DEFAULT NULL, care_task_id INT DEFAULT NULL, INDEX IDX_6000B0D3A76ED395 (user_id), INDEX IDX_6000B0D35765E605 (propagation_action_id), INDEX IDX_6000B0D3CDD79FB3 (care_task_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plant_notes (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, plant_id INT NOT NULL, INDEX IDX_F2C13F041D935652 (plant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plants (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, botanical_name VARCHAR(100) DEFAULT NULL, photo_path VARCHAR(255) DEFAULT NULL, light_requirement VARCHAR(255) NOT NULL, temperature_requirement VARCHAR(255) NOT NULL, humidity_requirement VARCHAR(255) NOT NULL, soil_type VARCHAR(50) DEFAULT NULL, pot_size VARCHAR(50) DEFAULT NULL, watering_interval_days INT DEFAULT NULL, fertilizing_interval_days INT DEFAULT NULL, repotting_interval_days INT DEFAULT NULL, last_watered_at DATETIME NOT NULL, last_fertilized_at DATETIME NOT NULL, last_repotted_at DATETIME NOT NULL, toxic_for_humans TINYINT NOT NULL, toxic_for_animals TINYINT NOT NULL, purchase_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, stress_score INT NOT NULL, died_at DATETIME DEFAULT NULL, user_id INT NOT NULL, location_id INT DEFAULT NULL, INDEX IDX_A5AEDC16A76ED395 (user_id), INDEX IDX_A5AEDC1664D218E (location_id), UNIQUE INDEX user_plant_name (user_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE propagation_actions (id INT AUTO_INCREMENT NOT NULL, method VARCHAR(255) NOT NULL, planned_date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, plant_id INT NOT NULL, INDEX IDX_6971206F1D935652 (plant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE task_assignments (id INT AUTO_INCREMENT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, assigned_at DATETIME NOT NULL, responded_at DATETIME NOT NULL, from_user_id INT NOT NULL, to_user_id INT NOT NULL, care_task_id INT NOT NULL, INDEX IDX_76FFFDEF2130303A (from_user_id), INDEX IDX_76FFFDEF29F6EE60 (to_user_id), INDEX IDX_76FFFDEFCDD79FB3 (care_task_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wishlist_plants (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) DEFAULT NULL, botanical_name VARCHAR(100) DEFAULT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL, location_id INT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_169C035064D218E (location_id), INDEX IDX_169C0350A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('ALTER TABLE care_history
                            ADD CONSTRAINT FK_67DBEE7E1D935652 FOREIGN KEY (plant_id) REFERENCES plants (id),
                            ADD CONSTRAINT FK_67DBEE7EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE care_task ADD CONSTRAINT FK_75110A221D935652 FOREIGN KEY (plant_id) REFERENCES plants (id)');
        $this->addSql('ALTER TABLE locations ADD CONSTRAINT FK_17E64ABAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notifications
                            ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id),
                            ADD CONSTRAINT FK_6000B0D35765E605 FOREIGN KEY (propagation_action_id) REFERENCES propagation_actions (id),
                            ADD CONSTRAINT FK_6000B0D3CDD79FB3 FOREIGN KEY (care_task_id) REFERENCES care_task (id)');
        $this->addSql('ALTER TABLE plant_notes ADD CONSTRAINT FK_F2C13F041D935652 FOREIGN KEY (plant_id) REFERENCES plants (id)');
        $this->addSql('ALTER TABLE plants
                            ADD CONSTRAINT FK_A5AEDC16A76ED395 FOREIGN KEY (user_id) REFERENCES user (id),
                            ADD CONSTRAINT FK_A5AEDC1664D218E FOREIGN KEY (location_id) REFERENCES locations (id)');
        $this->addSql('ALTER TABLE propagation_actions ADD CONSTRAINT FK_6971206F1D935652 FOREIGN KEY (plant_id) REFERENCES plants (id)');
        $this->addSql('ALTER TABLE task_assignments
                            ADD CONSTRAINT FK_76FFFDEF2130303A FOREIGN KEY (from_user_id) REFERENCES user (id),
                            ADD CONSTRAINT FK_76FFFDEF29F6EE60 FOREIGN KEY (to_user_id) REFERENCES user (id),
                            ADD CONSTRAINT FK_76FFFDEFCDD79FB3 FOREIGN KEY (care_task_id) REFERENCES care_task (id)');
        $this->addSql('ALTER TABLE wishlist_plants
                            ADD CONSTRAINT FK_169C035064D218E FOREIGN KEY (location_id) REFERENCES locations (id),
                            ADD CONSTRAINT FK_169C0350A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL, ADD is_minimal_mode TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE care_history DROP FOREIGN KEY FK_67DBEE7E1D935652');
        $this->addSql('ALTER TABLE care_history DROP FOREIGN KEY FK_67DBEE7EA76ED395');
        $this->addSql('ALTER TABLE care_task DROP FOREIGN KEY FK_75110A221D935652');
        $this->addSql('ALTER TABLE locations DROP FOREIGN KEY FK_17E64ABAA76ED395');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D35765E605');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3CDD79FB3');
        $this->addSql('ALTER TABLE plant_notes DROP FOREIGN KEY FK_F2C13F041D935652');
        $this->addSql('ALTER TABLE plants DROP FOREIGN KEY FK_A5AEDC16A76ED395');
        $this->addSql('ALTER TABLE plants DROP FOREIGN KEY FK_A5AEDC1664D218E');
        $this->addSql('ALTER TABLE propagation_actions DROP FOREIGN KEY FK_6971206F1D935652');
        $this->addSql('ALTER TABLE task_assignments DROP FOREIGN KEY FK_76FFFDEF2130303A');
        $this->addSql('ALTER TABLE task_assignments DROP FOREIGN KEY FK_76FFFDEF29F6EE60');
        $this->addSql('ALTER TABLE task_assignments DROP FOREIGN KEY FK_76FFFDEFCDD79FB3');
        $this->addSql('ALTER TABLE wishlist_plants DROP FOREIGN KEY FK_169C035064D218E');
        $this->addSql('ALTER TABLE wishlist_plants DROP FOREIGN KEY FK_169C0350A76ED395');
        $this->addSql('DROP TABLE care_history');
        $this->addSql('DROP TABLE care_task');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE plant_notes');
        $this->addSql('DROP TABLE plants');
        $this->addSql('DROP TABLE propagation_actions');
        $this->addSql('DROP TABLE task_assignments');
        $this->addSql('DROP TABLE wishlist_plants');
        $this->addSql('ALTER TABLE user DROP created_at, DROP is_minimal_mode');
    }
}
