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
WHERE
    `r`.`verdict` = "AC" AND `s`.`type` = "normal" AND
    FIND_IN_SET(`s`.`language`,'cpp,cpp11,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang')
GROUP BY
    `u`.`user_id`
HAVING
    COUNT(DISTINCT `p`.`problem_id`) > 10;
    