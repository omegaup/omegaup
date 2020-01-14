-- All the problems in assignments with no submissions will change their points value to 0
UPDATE
  Problemset_Problems p
INNER JOIN
  (
    SELECT
      p.title,
      psp.problemset_id,
      p.problem_id,
      psp.points,
      p.languages
    FROM
      Problemsets ps
    INNER JOIN
      Problemset_Problems psp
    ON
      psp.problemset_id = ps.problemset_id
    INNER JOIN
      Problems p
    ON
      p.problem_id = psp.problem_id
    WHERE
      ps.type = 'Assignment'
    AND p.languages = ''
  ) q
ON
  p.problemset_id = q.problemset_id AND p.problem_id = q.problem_id
SET
  p.points = 0;

-- Recalculate max_points to all affected assignments
UPDATE
  Assignments a
JOIN
  (
    SELECT
      assignment_id,
      sum(psp.points) AS max_points
    FROM
      Assignments a
    INNER JOIN
      Problemset_Problems psp
    ON
      a.problemset_id = psp.problemset_id
    GROUP BY
      a.assignment_id
  ) q
ON
  a.assignment_id = q.assignment_id
SET
  a.max_points = q.max_points;
