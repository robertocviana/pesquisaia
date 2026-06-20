-- Migration 005: respondents
CREATE TABLE IF NOT EXISTS `respondents` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id`  INT UNSIGNED NOT NULL,
    `token`      VARCHAR(64)  NOT NULL,
    `name`       VARCHAR(150)          DEFAULT NULL,
    `status`     ENUM('em_andamento','concluida') NOT NULL DEFAULT 'em_andamento',
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `respondents_token_unique` (`survey_id`, `token`),
    KEY `respondents_survey_id_idx` (`survey_id`),
    CONSTRAINT `fk_respondents_survey`
        FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
