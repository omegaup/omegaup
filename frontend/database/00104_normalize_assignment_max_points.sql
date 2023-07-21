-- Update max_points for existing assignments created via clone tool
UPDATE
    `Assignments` a
INNER JOIN
    (
        SELECT
            pp.problemset_id,
            SUM(pp.points) as `sum_points`
        FROM
            `Problemset_Problems` pp
        GROUP BY
            pp.problemset_id
    ) pp
ON
    a.problemset_id = pp.problemset_id
SET
    a.`max_points` = pp.`sum_points`
WHERE
    a.`max_points` = 0;
