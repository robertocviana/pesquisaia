-- Migration 002: surveys
CREATE TABLE IF NOT EXISTS `surveys` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED NOT NULL,
    `name`           VARCHAR(255) NOT NULL DEFAULT 'Nova pesquisa',
    `objective`      TEXT         NOT NULL DEFAULT '',
    `audience`       VARCHAR(255)          DEFAULT NULL,
    `status`         ENUM('rascunho','ativa','encerrada') NOT NULL DEFAULT 'rascunho',
    `goal_responses` SMALLINT UNSIGNED     DEFAULT NULL,
    `deadline_at`    DATE                  DEFAULT NULL,
    `public_slug`    VARCHAR(32)           DEFAULT NULL,
    `response_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `surveys_public_slug_unique` (`public_slug`),
    KEY `surveys_user_id_idx` (`user_id`),
    KEY `surveys_status_idx` (`status`),
    CONSTRAINT `fk_surveys_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
