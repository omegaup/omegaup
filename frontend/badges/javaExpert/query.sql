SELECT
    `u`.`user_id`
FROM
    `Submissions` AS `s`
INNER JOIN
    `Problems` AS `p` ON `p`.`problem_id` = `s`.`problem_id` AND `p`.`visibility` >= 2
INNER JOIN
    `Identities` AS `i` ON `s`.`identity_id` = `i`.`identity_id`
INNER JOIN
    `Users` AS `u` ON `u`.`main_identity_id` = `i`.`identity_id`
WHERE
    `s`.`verdict` = 'AC' AND
    `s`.`type` = 'normal' AND
    `s`.`language` = 'java'
GROUP BY
    `u`.`user_id`
HAVING
    COUNT(DISTINCT `p`.`problem_id`) > 10;
