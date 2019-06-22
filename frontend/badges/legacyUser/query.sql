SELECT
    DISTINCT `u`.`user_id`
FROM
    `Submissions` AS `s`
INNER JOIN
    `Identities` AS `i` ON `s`.`identity_id` = `i`.`identity_id`
INNER JOIN
    `Users` AS `u` ON `u`.`main_identity_id` = `i`.`identity_id`
WHERE
    `s`.`time` <= '2013-02-02 13:12:24';