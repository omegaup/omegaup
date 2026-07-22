SELECT
    `u`.`user_id`
FROM
    `Submissions` AS `s`
INNER JOIN
    `Problems` AS `p` ON `p`.`problem_id` = `s`.`problem_id`
INNER JOIN
    `Users` AS `u` ON `u`.`main_identity_id` = `s`.`identity_id`
WHERE
    `s`.`verdict` = 'AC' AND
    `s`.`type` = 'normal' AND
    `p`.`visibility` >= 2 AND
    `s`.`language` IN ('cpp', 'cpp11', 'cpp11-gcc', 'cpp11-clang', 'cpp17-gcc', 'cpp17-clang', 'cpp20-gcc', 'cpp20-clang', 'c11-gcc', 'c11-clang')
GROUP BY
    `u`.`user_id`
HAVING
    COUNT(DISTINCT `p`.`problem_id`) > 10;
    