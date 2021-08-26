SELECT
    DISTINCT u.user_id
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
    Users AS u ON u.main_identity_id = i.identity_id
INNER JOIN
    Courses c ON c.course_id = a.course_id
WHERE
    c.alias = 'ResolviendoProblemas2021' AND
    r.verdict = 'AC' AND
    i.user_id IS NOT NULL
GROUP BY
    u.user_id
HAVING
    COUNT(DISTINCT psp.problem_id) >= 1;