/* Arregalndo los problemas que esta en warning pero no tienen ninguna nominacion con estatus warning*/
UPDATE
    `Problems` AS `p`
SET
    `visibility` = 2
WHERE
    `visibility` = 1 AND
    (
        SELECT
            COUNT(`qualitynomination_id`)
		FROM
			`QualityNominations`
		WHERE
			(`status`='warning') AND
            `nomination`='demotion' AND
			`problem_id`=`p`.`problem_id`
    )=0
;

/*Need next line to disable safe update*/
SET SQL_SAFE_UPDATES = 0;

/* Arregalndo el user_id y el from_status*/
UPDATE
	`QualityNomination_Log`
SET
	`from_status` = CASE
    WHEN `from_status`=`to_status` THEN 'open'
    ELSE `from_status`
    END,
    `user_id` = CASE
        WHEN `user_id` THEN (SELECT `user_id` FROM `Identities` WHERE `username`='RuizYugen' LIMIT 1 )
        ELSE `user_id`
    END
WHERE
	`time`< '2020-04-26';

SET SQL_SAFE_UPDATES = 1;