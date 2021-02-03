/*adding fields for the column status: warning, resolved and banned*/
ALTER TABLE
    `QualityNominations`
CHANGE COLUMN
    `status` `status` ENUM('open','approved','denied','warning','resolved','banned') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación' ;

/*adding fields for the columns to_status and from_status: warning, resolved and banned*/
ALTER TABLE
    `QualityNomination_Log`
CHANGE COLUMN
    `to_status` `to_status` ENUM('open','approved','denied','warning','resolved','banned') NOT NULL DEFAULT 'open',
CHANGE COLUMN
    `from_status` `from_status` ENUM('open','approved','denied','warning','resolved','banned') NOT NULL DEFAULT 'open';

/*Change values of column status, from denied to banned  and from approved to resolved*/
UPDATE
    `QualityNominations`
SET
    `status` = 'banned'
WHERE
    `status` = 'denied';
UPDATE
    `QualityNominations`
SET
    `status` = 'resolved'
WHERE
    `status` = 'approved';

/*Change values of column to_status, from denied to banned  and from approved to resolved*/
UPDATE
    `QualityNomination_Log`
SET
    `to_status` = 'banned'
WHERE
    `to_status` = 'denied';

UPDATE
    `QualityNomination_Log`
SET
    `to_status` = 'resolved'
WHERE
`to_status` = 'approved';

/*Change values of column from_status, from denied to banned  and from approved to resolved*/
UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'banned'
WHERE
    `from_status` = 'denied';

UPDATE
    `QualityNomination_Log`
SET
    `from_status` = 'resolved'
WHERE
    `from_status` = 'approved';

/*deleting fields for the column status: approved, denied*/
ALTER TABLE
    `QualityNominations`
CHANGE COLUMN
    `status` `status` ENUM('open','warning','resolved','banned') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación' ;

/*deleting fields for the columns to_status and from_status: approved, denied*/
ALTER TABLE
    `QualityNomination_Log`
CHANGE COLUMN
    `to_status` `to_status` ENUM('open','warning','resolved','banned') NOT NULL DEFAULT 'open',
CHANGE COLUMN
    `from_status` `from_status` ENUM('open','warning','resolved','banned') NOT NULL DEFAULT 'open';

/*changing the visibility values for the new values
PRIVATE_BANNED from -2 to -3*/
UPDATE
    `Problems`
SET
    `visibility` = '-3'
WHERE
    `visibility` = '-2';

/*PUBLIC_BANNED from -1 to -2*/
UPDATE
    `Problems`
SET
    `visibility` = '-2'
WHERE
    `visibility` = '-1';

/*PROMOTED from 2 to 3*/
UPDATE
    `Problems`
SET
    `visibility` = '3'
WHERE
    `visibility` = '2';

/*PUBLIC from 1 to 2*/
UPDATE
    `Problems`
SET
    `visibility` = '2'
WHERE
    `visibility` = '1';
