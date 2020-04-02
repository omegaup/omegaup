SELECT
    u2.user_id
FROM
    Users AS u2
INNER JOIN
(
    SELECT
        DISTINCT user_id,
        sum(count) OVER (PARTITION BY user_id, time) AS count,
        time
    FROM
    (
        (
            SELECT
                DISTINCT u.user_id,
                year(c.last_updated) AS time,
                count(c.contest_id) OVER (PARTITION BY year(c.last_updated)) AS count
            FROM
                Users AS u
            INNER JOIN
                ACLs AS a ON a.owner_id = u.user_id
            INNER JOIN
                Contests AS c ON c.acl_id = a.acl_id
            WHERE
                year(c.last_updated) >= year(now())-2
        )
        UNION ALL
        (
            SELECT
                DISTINCT u.user_id,
                year(p.creation_date) AS time,
                count(p.problem_id) OVER (PARTITION BY year(p.creation_date)) AS count
            FROM
                Users AS u
            INNER JOIN
                ACLs AS a ON a.owner_id = u.user_id
            INNER JOIN
                Problems AS p ON p.acl_id = a.acl_id
            WHERE
                year(p.creation_date) >= year(now())-2
        )
        UNION ALL
        (
            SELECT
                DISTINCT u.user_id,
                year(s.time) AS time,
                count(s.problem_id) OVER (PARTITION BY year(s.time)) AS count
            FROM
                Users AS u
            INNER JOIN
                Submissions AS s ON s.identity_id = u.main_identity_id
            INNER JOIN
                Runs AS r ON r.run_id = s.current_run_id
            WHERE
                r.verdict = 'AC' AND year(s.time) >= year(now())-2
        )
    ) valores
    GROUP BY
        valores.user_id,
        valores.count,
        valores.time
) AS total ON total.user_id = u2.user_id
GROUP BY
    u2.user_id
HAVING
    count(total.user_id) = 3;
