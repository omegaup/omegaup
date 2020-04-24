DELETE FROM
	`Users_Badges`
WHERE
	`badge_alias` = 'coderOfTheMonth' AND
    (
        SELECT
	        COUNT(`cotm`.`user_id`)
        FROM
	        `Coder_Of_The_Month` AS `cotm`
        WHERE
	    `cotm`.`ranking` = 1 AND
	    `cotm`.`user_id` = `user_id`
    ) = 0;