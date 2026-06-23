-- Migration 010: alter deadline_at in surveys to DATETIME
ALTER TABLE `surveys` MODIFY COLUMN `deadline_at` DATETIME DEFAULT NULL;
