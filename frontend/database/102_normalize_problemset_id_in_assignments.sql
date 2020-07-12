-- Normalize problemset_id in assignments
UPDATE
    `Problemsets`
INNER JOIN
    `Assignments`
ON
    `Problemsets`.`problemset_id` = `Assignments`.`problemset_id`
SET
    `Problemsets`.`acl_id` = `Assignments`.`acl_id`;