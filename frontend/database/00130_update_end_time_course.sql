-- Update end_time for existing users that belong to a course
UPDATE
    `Problemset_Identities`
INNER JOIN
    `Assignments`
ON
    Assignments.problemset_id = Problemset_Identities.problemset_id
SET
    `end_time` = `Assignments`.`finish_time`;
