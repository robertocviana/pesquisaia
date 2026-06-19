-- Seed 001: usuário de desenvolvimento
-- Senha: password (hash bcrypt cost 12, gerado via PHP)
INSERT IGNORE INTO `users` (`name`, `email`, `password_hash`) VALUES
(
    'Dev User',
    'dev@pesquisaia.com',
    '$2y$12$5aNGoB1pGdm.8HOuTJ.J6evtpNA3Llfn7uDgzF.X09x/wENe.eYrK'
);
