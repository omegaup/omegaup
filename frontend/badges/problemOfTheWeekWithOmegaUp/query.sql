SELECT
    u.user_id
FROM
    Submissions s
INNER JOIN
    Assignments a ON a.problemset_id = s.problemset_id
INNER JOIN
    Problemset_Problems psp ON psp.problemset_id = a.problemset_id AND psp.problem_id = s.problem_id
INNER JOIN
    Identities i ON i.identity_id = s.identity_id AND i.user_id IS NOT NULL
INNER JOIN
    Users u ON u.main_identity_id = i.identity_id
INNER JOIN
    Courses c ON c.course_id = a.course_id
WHERE
    c.alias = 'ResolviendoProblemas2021' AND
    s.verdict = 'AC'
GROUP BY
    u.user_id
HAVING
    COUNT(DISTINCT psp.problem_id) >= 1;