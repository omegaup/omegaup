ALTER TABLE
    `QualityNominations`
MODIFY
    `nomination` enum('suggestion', 'quality_category', 'promotion','demotion','dismissal') NOT NULL DEFAULT 'suggestion' COMMENT 'El tipo de nominación';
