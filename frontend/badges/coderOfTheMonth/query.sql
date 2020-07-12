SELECT
    DISTINCT `cotm`.`user_id`
FROM
    `Coder_Of_The_Month` AS `cotm`
WHERE
    `cotm`.`ranking` = 1 AND
    `cotm`.`time` <= NOW();