/*adding field to state because it needs to preserve correct values */
ALTER TABLE
    `QualityNominations`
CHANGE COLUMN
    `status` `status` ENUM('open','warning','resolved','banned','temporal') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación' ;

ALTER TABLE
    `QualityNomination_Log`
CHANGE COLUMN
    `to_status` `to_status` ENUM('open','warning','resolved','banned','temporal') NOT NULL DEFAULT 'open',
CHANGE COLUMN
    `from_status` `from_status` ENUM('open','warning','resolved','banned','temporal') NOT NULL DEFAULT 'open';
/*Need next line to disable safe update*/
SET SQL_SAFE_UPDATES = 0;

/*update QualityNominations banned to temporal only nominations
that were before del PR https://github.com/omegaup/omegaup/pull/3736*/
UPDATE
    `QualityNominations` AS `q`
SET
    `q`.`status` = 'temporal'
WHERE
    `q`.`status` = 'banned' AND
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

/*update QualityNominations resolved to banned  only nominations
that were before del PR https://github.com/omegaup/omegaup/pull/3736*/
UPDATE
    `QualityNominations` AS `q`
SET
    `q`.`status` = 'banned'
WHERE
    `q`.`status` = 'resolved' AND
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

/*update QualityNominations temporal to resolved*/
UPDATE
    `QualityNominations`
SET
    `status` = 'resolved'
WHERE
    `status` = 'temporal';

/*update QualityNominations banned to temporal only nominations
that were before del PR https://github.com/omegaup/omegaup/pull/3736*/
UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'temporal'
WHERE
    `from_status` = 'banned' AND
    `time`< '2020-04-26';

UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'temporal'
WHERE
    `from_status` = 'banned' AND
    `time`< '2020-04-26';

/*update QualityNominations resolved to banned  only nominations
that were before del PR https://github.com/omegaup/omegaup/pull/3736*/
UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'banned'
WHERE
    `from_status` = 'resolved' AND
    `time`< '2020-04-26';

UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'banned'
WHERE
    `from_status` = 'resolved' AND
    `time`< '2020-04-26';

/*update QualityNominations temporal to resolved*/
UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'resolved'
WHERE
    `from_status` = 'temporal' AND
    `time`< '2020-04-26';

UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'resolved'
WHERE
    `from_status` = 'temporal' AND
    `time`< '2020-04-26';

/*leave everything as it was*/
ALTER TABLE
    `QualityNominations`
CHANGE COLUMN
    `status` `status` ENUM('open','warning','resolved','banned') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación' ;

ALTER TABLE
    `QualityNomination_Log`
CHANGE COLUMN
    `to_status` `to_status` ENUM('open','warning','resolved','banned') NOT NULL DEFAULT 'open',
CHANGE COLUMN
    `from_status` `from_status` ENUM('open','warning','resolved','banned') NOT NULL DEFAULT 'open';
SET SQL_SAFE_UPDATES = 1;