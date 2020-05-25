/*Need next line to disable safe update*/
SET SQL_SAFE_UPDATES = 0;

/*update QualityNominations (banned to resolved and resolved to banned) only QualityNominations
that were before del PR https://github.com/omegaup/omegaup/pull/3736*/
UPDATE
	`QualityNominations` AS `q`
SET
	`q`.`status` = CASE
WHEN
	`q`.`status` = 'banned'
THEN
	'resolved'
WHEN
	`q`.`status` = 'resolved'
THEN
	'banned'
END
WHERE
    (
    	SELECT
    		`qualitynomination_id`
    	FROM
    		`QualityNomination_Log`
    	WHERE
    		`qualitynomination_id`= `q`.`qualitynomination_id` AND
    		`time`< '2020-04-26'
    	ORDER BY
    		`time` DESC
    	LIMIT 1
    ) != null;

/*update QualityNominationLog resolved to banned  only nominations
that were before del PR https://github.com/omegaup/omegaup/pull/3736*/
UPDATE
	`QualityNomination_Log`
SET
	`from_status` = CASE
WHEN
	`from_status` = 'banned' AND
    `time`< '2020-04-26'
THEN
	'resolved'
WHEN
	`from_status` = 'resolved' AND
    `time`< '2020-04-26'
THEN
	'banned'
END;

UPDATE
	`QualityNomination_Log`
SET
	`to_status` = CASE
WHEN
	`to_status` = 'banned' AND
    `time`< '2020-04-26'
THEN
	'resolved'
WHEN
	`to_status` = 'resolved' AND
    `time`< '2020-04-26'
THEN
	'banned'
END;

SET SQL_SAFE_UPDATES = 1;