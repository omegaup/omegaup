SELECT
    DISTINCT `u`.`user_id`
FROM
    `Identity_Login_Log` AS `ill`
INNER JOIN
    `Identities` AS `i` ON `i`.`identity_id` = `ill`.`identity_id`
INNER JOIN
    `Users` AS `u` ON `u`.`main_identity_id` = `i`.`identity_id`
WHERE
    YEAR(`ill`.`time` <= 2012);