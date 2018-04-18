-- Problemsets
ALTER TABLE `Problemsets`
    ADD COLUMN `type` enum('Contest', 'Assignment', 'Interview') NOT NULL DEFAULT 'Contest'
        COMMENT 'Almacena el tipo de problemset que se ha creado';

-- Populating the new field
UPDATE
    Problemsets p
LEFT JOIN
    Assignments a
ON
    p.problemset_id = a.problemset_id
LEFT JOIN
    Courses cu
ON
    a.course_id = cu.course_id
LEFT JOIN
    Contests c
ON
    p.problemset_id = c.problemset_id
LEFT JOIN
    Interviews i
ON
    p.problemset_id = i.problemset_id
SET
    type = IF(i.interview_id IS NOT NULL, "Interview",
    	IF(a.assignment_id IS NOT NULL, "Assignment", "Contest"));
