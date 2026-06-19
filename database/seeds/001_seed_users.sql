-- Seed 001: usuário de desenvolvimento
-- Senha: password (hash bcrypt)
INSERT IGNORE INTO `users` (`name`, `email`, `password_hash`) VALUES
(
    'Dev User',
    'dev@pesquisaia.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);
