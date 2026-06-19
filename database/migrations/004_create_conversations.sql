-- Migration 004: conversations (histórico do chat de criação)
CREATE TABLE IF NOT EXISTS `conversations` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id`  INT UNSIGNED NOT NULL,
    `role`       ENUM('user','assistant') NOT NULL,
    `content`    TEXT         NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `conversations_survey_id_idx` (`survey_id`),
    CONSTRAINT `fk_conversations_survey`
        FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
