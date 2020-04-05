SELECT
    DISTINCT `u`.`user_id`
FROM
    `Identities` AS `i`
INNER JOIN
    `Users` AS `u` ON `i`.`user_id` = `u`.`user_id`
INNER JOIN
    `Identities_Schools` AS `is` ON `i`.`current_identity_school_id` = `is`.`identity_school_id`
INNER JOIN
    `Schools` AS `s` ON `s`.`school_id` = `is`.`school_id`
WHERE
    `i`.`language_id` IS NOT NULL AND
    `i`.`country_id`IS NOT NULL AND
    `i`.`state_id` IS NOT NULL AND
    `i`.`gender` IS NOT NULL AND
    `i`.`current_identity_school_id` IS NOT NULL AND
    `u`.`scholar_degree` IS NOT NULL AND
    `u`.`preferred_language` IS NOT NULL AND
    `u`.`birth_date` IS NOT NULL AND
    `is`.`graduation_date` IS NOT NULL;
