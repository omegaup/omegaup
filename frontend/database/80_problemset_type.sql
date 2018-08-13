-- Problemsets
ALTER TABLE `Problemsets`
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
    p.assignment_id = a.assignment_id;

UPDATE
    Problemsets p
INNER JOIN
    Contests c
ON
    p.problemset_id = c.problemset_id
SET
    c.contest_id = c.contest_id;

UPDATE
    Problemsets p
INNER JOIN
    Interviews i
ON
    p.problemset_id = i.problemset_id
SET
    i.interview_id = i.interview_id;
