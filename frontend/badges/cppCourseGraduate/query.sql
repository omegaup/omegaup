SELECT
	p.user_id
FROM 
    (
        SELECT
            a.alias,
            a.assignment_id,
            psp.problem_id,
            i.user_id,
            sum((r.contest_score)) as best_score_of_problem
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
            a.assignment_id, psp.problem_id, s.identity_id
    ) p
	GROUP BY
        p.user_id
    HAVING
        SUM(p.best_score_of_problem)>=
                                        (
                                            SELECT
                                                SUM(psp.points)*.6
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
