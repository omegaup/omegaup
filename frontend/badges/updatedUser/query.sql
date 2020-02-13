SELECT
    DISTINCT `u`.`user_id`
FROM
    `Identities` As `i`
INNER JOIN
    `Users` AS `u` ON `i`.`user_id` = `u`.`user_id`
INNER JOIN
    `Identities_Schools` AS `s` ON `s`.`identity_id` = `i`.`identity_id`
WHERE
    `i`.`language_id` IS NOT NULL AND
    `i`.`country_id`IS NOT NULL AND
    `i`.`state_id` IS NOT NULL AND
    `i`.`gender` IS NOT NULL AND
    `i`.`current_identity_school_id` IS NOT NULL AND
    `u`.`scholar_degree` IS NOT NULL AND
    `u`.`preferred_language` IS NOT NULL AND
    `u`.`birth_date` IS NOT NULL AND
    `s`.`graduation_date` IS NOT NULL
GROUP BY
    `u`.`user_id`;
