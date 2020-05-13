SELECT
   u.user_id
from
	Users u
INNER JOIN (
    SELECT
        i.user_id,
        MAX(r.contest_score) AS scoreMax,
        psp.problem_id
    FROM
        Assignments a
    INNER JOIN
        Problemsets ps ON a.problemset_id = ps.problemset_id
    INNER JOIN
        Problemset_Problems psp ON psp.problemset_id = ps.problemset_id
    INNER JOIN
        Submissions s ON s.problem_id = psp.problem_id AND
        s.problemset_id = a.problemset_id
    INNER JOIN
        Runs r ON r.run_id = s.current_run_id
    INNER JOIN
        Identities i ON i.identity_id = s.identity_id
    INNER JOIN
        Courses c ON c.course_id = a.course_id
    WHERE
        c.alias = 'introduccion_a_cpp'
    GROUP BY
        i.user_id, psp.problem_id
    ) p on p.user_id = u.user_id
GROUP BY
    u.user_id
HAVING
    SUM(p.scoreMax) >= (
        SELECT
            SUM(psp.points) * 0.6
        FROM
            Courses c
        INNER JOIN
            Assignments a ON a.course_id = c.course_id
        INNER JOIN
            Problemsets ps ON a.problemset_id = ps.problemset_id
        INNER JOIN
            Problemset_Problems psp ON psp.problemset_id = ps.problemset_id
        WHERE
            c.alias = 'introduccion_a_cpp'
    );