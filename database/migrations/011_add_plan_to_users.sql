-- Migration 011: add plan to users
ALTER TABLE `users` ADD COLUMN `plan` ENUM('trial', 'pro') NOT NULL DEFAULT 'trial';
