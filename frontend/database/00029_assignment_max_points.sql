ALTER TABLE `Assignments`
    ADD COLUMN `max_points` double NOT NULL DEFAULT '0' COMMENT 'La cantidad total de puntos que se pueden obtener.' AFTER `finish_time`;

UPDATE `Assignments`
LEFT JOIN (
    SELECT a.assignment_id, sum(psp.points) as max_points
    FROM Assignments a
    INNER JOIN Problemset_Problems psp
        ON a.problemset_id = psp.problemset_id
    GROUP BY a.assignment_id
) q
ON `Assignments`.`assignment_id` = q.`assignment_id`
SET `Assignments`.`max_points` = IFNULL(q.`max_points`, 0);

CREATE INDEX idx_problemset_problems_ids ON Problemset_Problems (`problem_id`, `problemset_id`);
