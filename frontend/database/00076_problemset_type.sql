-- Problemsets
ALTER TABLE `Problemsets`
    ADD COLUMN `type` enum('Contest', 'Assignment', 'Interview') DEFAULT NULL;

-- Populating the new field
UPDATE
    Problemsets p
INNER JOIN
    Assignments a
ON
    p.problemset_id = a.problemset_id
SET
    type = "Assignment";

UPDATE
    Problemsets p
INNER JOIN
    Contests c
ON
    p.problemset_id = c.problemset_id
SET
    type = "Contest";

UPDATE
    Problemsets p
INNER JOIN
    Interviews i
ON
    p.problemset_id = i.problemset_id
SET
    type = "Interview";

-- Problemsets
ALTER TABLE `Problemsets`
    MODIFY COLUMN `type` enum('Contest', 'Assignment', 'Interview') NOT NULL DEFAULT 'Contest'
        COMMENT 'Almacena el tipo de problemset que se ha creado';
