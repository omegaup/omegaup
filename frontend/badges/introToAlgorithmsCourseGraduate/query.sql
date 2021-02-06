SELECT
    u.user_id
FROM
    Users u
INNER JOIN (
    SELECT
        i.user_id,
        MAX(r.contest_score) AS max_contest_score,
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
        c.alias = 'introduccion_a_algoritmos' AND
        i.user_id IS NOT NULL
    GROUP BY
        i.user_id, psp.problem_id
    ) p ON p.user_id = u.user_id
GROUP BY
    u.user_id
HAVING
    SUM(p.max_contest_score) >= (
        SELECT
            SUM(psp.points) * 0.7
        FROM
            Courses c
        INNER JOIN
            Assignments a ON a.course_id = c.course_id
        INNER JOIN
            Problemsets ps ON a.problemset_id = ps.problemset_id
        INNER JOIN
            Problemset_Problems psp ON psp.problemset_id = ps.problemset_id
        WHERE
            c.alias = 'introduccion_a_algoritmos'
    );