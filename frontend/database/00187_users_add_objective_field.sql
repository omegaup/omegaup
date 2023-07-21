ALTER TABLE
    `Users`
ADD
    COLUMN `has_learning_objective` tinyint(1) NULL DEFAULT NULL
    COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para aprender.'
    AFTER `main_identity_id`,
ADD
    COLUMN `has_teaching_objective` tinyint(1) NULL DEFAULT NULL
    COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para enseñar.'
    AFTER `has_learning_objective`,
ADD
    COLUMN `has_scholar_objective` tinyint(1) NULL DEFAULT NULL
    COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para la escuela.'
    AFTER `has_teaching_objective`,
ADD
    COLUMN `has_competitive_objective` tinyint(1) NULL DEFAULT NULL
    COMMENT 'Dice si el usuario expresó tener el objetivo de usar omegaUp para programación competitiva.'
    AFTER `has_scholar_objective`;
