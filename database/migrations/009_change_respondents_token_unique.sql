-- Migration 009: change respondents token unique constraint
ALTER TABLE `respondents` DROP INDEX `respondents_token_unique`;
ALTER TABLE `respondents` ADD UNIQUE KEY `respondents_token_unique` (`survey_id`, `token`);
