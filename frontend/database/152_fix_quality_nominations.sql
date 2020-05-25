/*Need next line to disable safe update*/
SET SQL_SAFE_UPDATES = 0;

/*Updating QualityNominations and QualityNomination_Log status, from_status and to_status
(banned to resolved and resolved to banned) only QualityNominations
that were before del PR https://github.com/omegaup/omegaup/pull/3736*/
UPDATE
	`QualityNominations` AS `q`
INNER JOIN
	`QualityNomination_Log` AS `ql` ON `q`.`qualitynomination_id` = `ql`.`qualitynomination_id`
SET
	`q`.`status` = CASE
    WHEN `q`.`status` = 'banned' THEN 'resolved'
    WHEN `q`.`status` = 'resolved' THEN 'banned'
    ELSE `q`.`status`
    END,
    `ql`.`from_status` = CASE
        WHEN `ql`.`from_status` = 'banned' THEN 'resolved'
        WHEN `ql`.`from_status` = 'resolved' THEN 'banned'
        ELSE `ql`.`from_status`
    END,
    `ql`.`to_status` = CASE
        WHEN `ql`.`to_status` = 'banned' THEN 'resolved'
        WHEN `ql`.`to_status` = 'resolved' THEN 'banned'
        ELSE `ql`.`to_status`
     END
WHERE
	`ql`.`time`< '2020-04-26';

SET SQL_SAFE_UPDATES = 1;