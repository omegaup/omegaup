SELECT
    DISTINCT `ur`.`user_id`
FROM
    `User_Rank` AS `ur`
WHERE
    `ur`.`score` >= 500;