-- Migration 007: reports (cache do relatório gerado por IA)
CREATE TABLE IF NOT EXISTS `reports` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `survey_id`    INT UNSIGNED NOT NULL,
    `summary`      TEXT                  DEFAULT NULL,
    `insights`     JSON                  DEFAULT NULL,
    `generated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `reports_survey_id_unique` (`survey_id`),
    CONSTRAINT `fk_reports_survey`
        FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
