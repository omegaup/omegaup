-- QualityNominations
ALTER TABLE `QualityNominations`
  MODIFY COLUMN `nomination` enum('suggestion', 'promotion', 'demotion', 'dismissal') NOT NULL DEFAULT 'suggestion' COMMENT 'El tipo de nominación';