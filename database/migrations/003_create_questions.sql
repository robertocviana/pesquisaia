-- Migration 003: questions
CREATE TABLE IF NOT EXISTS `questions` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id`   INT UNSIGNED NOT NULL,
    `order_index` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `text`        TEXT         NOT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `questions_survey_id_idx` (`survey_id`),
    CONSTRAINT `fk_questions_survey`
        FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
