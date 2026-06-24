-- Migration 012: add role to users
ALTER TABLE `users` ADD COLUMN `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user';

-- Define o usuário padrão do seed como admin para fins de teste local
UPDATE `users` SET `role` = 'admin' WHERE `email` = 'dev@pesquisaia.com';
