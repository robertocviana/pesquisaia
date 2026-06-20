-- Migration 008: add current_stage to surveys
ALTER TABLE `surveys` ADD COLUMN `current_stage` VARCHAR(32) NOT NULL DEFAULT 'tipo' AFTER `status`;
