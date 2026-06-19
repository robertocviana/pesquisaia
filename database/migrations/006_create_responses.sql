-- Migration 006: responses
CREATE TABLE IF NOT EXISTS `responses` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `respondent_id` INT UNSIGNED NOT NULL,
    `question_id`   INT UNSIGNED NOT NULL,
    `text_response` TEXT         NOT NULL,
    `answered_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `responses_respondent_id_idx` (`respondent_id`),
    KEY `responses_question_id_idx` (`question_id`),
    CONSTRAINT `fk_responses_respondent`
        FOREIGN KEY (`respondent_id`) REFERENCES `respondents` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_responses_question`
        FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
