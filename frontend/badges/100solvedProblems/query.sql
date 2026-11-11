SELECT
    DISTINCT `u`.`user_id`
FROM
    `Problems` AS `p`
INNER JOIN
    `Submissions` AS `s` ON `p`.`problem_id` = `s`.`problem_id`
INNER JOIN
    `Runs` AS `r` ON `r`.`run_id` = `s`.`current_run_id`
INNER JOIN
    `Identities` AS `i` ON `s`.`identity_id` = `i`.`identity_id`
INNER JOIN
    `Users` AS `u` ON `u`.`main_identity_id` = `i`.`identity_id`
LEFT JOIN
    `Problems_Forfeited` AS `pf` ON `pf`.`problem_id` = `p`.`problem_id`
    AND `pf`.`user_id` = `u`.`user_id`
LEFT JOIN
    `ACLs` AS `a` ON `a`.`acl_id` = `p`.`acl_id`
    AND `a`.`owner_id` = `u`.`user_id`
WHERE
    `r`.`verdict` = "AC" AND `s`.`type` = "normal"
    AND `pf`.`problem_id` IS NULL
    AND `a`.`acl_id` IS NULL
GROUP BY
    `u`.`user_id`
HAVING
    COUNT(DISTINCT `p`.`problem_id`) >= 100;