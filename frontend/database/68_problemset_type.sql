-- Problemsets
ALTER TABLE `Problemsets`
    ADD COLUMN `type` enum('Contest','Assignment','Interview') DEFAULT NULL,
    ADD COLUMN `contest_id` int(11) DEFAULT NULL COMMENT 'Id del concurso',
    ADD COLUMN `assignment_id` int(11) DEFAULT NULL COMMENT 'Id del curso',
    ADD COLUMN `interview_id` int(11) DEFAULT NULL COMMENT 'Id de la entrevista',
    ADD CONSTRAINT UNIQUE (`problemset_id`, `contest_id`, `assignment_id`, `interview_id`),
    ADD CONSTRAINT CHECK (`contest_id` IS NOT NULL OR `assignment_id` IS NOT NULL OR `interview_id` IS NOT NULL),
    ADD FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    ADD FOREIGN KEY (`assignment_id`) REFERENCES `Assignments` (`assignment_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    ADD FOREIGN KEY (`interview_id`) REFERENCES `Interviews` (`interview_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Populating the new field
UPDATE
    Problemsets p
INNER JOIN
    Assignments a
ON
    p.problemset_id = a.problemset_id
SET
    `type` = 'Assignment',
    p.assignment_id = a.assignment_id;

UPDATE
    Problemsets p
INNER JOIN
    Contests c
ON
    p.problemset_id = c.problemset_id
SET
    `type` = 'Contest',
    c.contest_id = c.contest_id;

UPDATE
    Problemsets p
INNER JOIN
    Interviews i
ON
    p.problemset_id = i.problemset_id
SET
    `type` = 'Interview',
    i.interview_id = i.interview_id;

-- Problemsets
ALTER TABLE `Problemsets`
    MODIFY COLUMN `type` enum('Contest', 'Assignment', 'Interview') NOT NULL DEFAULT 'Contest'
        COMMENT 'Almacena el tipo de problemset que se ha creado';
