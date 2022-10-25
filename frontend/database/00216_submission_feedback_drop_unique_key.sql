ALTER TABLE
    `Submission_Feedback`
DROP foreign key
    `fk_sfs_submission_id`;

ALTER TABLE
    `Submission_Feedback`
DROP INDEX
    `submission_id`;

ALTER TABLE
    `Submission_Feedback`
ADD CONSTRAINT
    `fk_sfs_submission_id`
FOREIGN KEY
    (`submission_id`)
REFERENCES
    `Submissions` (`submission_id`);