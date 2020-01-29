ALTER TABLE
    `QualityNominations`
MODIFY
    `nomination` enum('suggestion', 'quality_tag', 'promotion','demotion','dismissal') NOT NULL DEFAULT 'suggestion' COMMENT 'El tipo de nominación';
