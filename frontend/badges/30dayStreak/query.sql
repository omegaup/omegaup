WITH DailySubmissions AS (
    SELECT
        u.user_id,
        DATE(r.time) AS submission_date
    FROM Runs AS r
    JOIN Submissions AS s ON r.submission_id = s.submission_id
    JOIN Identities AS i ON s.identity_id = i.identity_id
    JOIN Users AS u ON i.user_id = u.user_id
    GROUP BY u.user_id, DATE(r.time)
),
ConsecutiveStreaks AS (
    SELECT
        ds.user_id,
        ds.submission_date,
        DENSE_RANK() OVER (PARTITION BY ds.user_id ORDER BY ds.submission_date) AS d_rank
    FROM DailySubmissions ds
),
GroupedStreaks AS (
    SELECT
        user_id,
        submission_date,
        submission_date - INTERVAL d_rank DAY AS streak_group
    FROM ConsecutiveStreaks
)
SELECT DISTINCT user_id
FROM (
    SELECT user_id, COUNT(*) AS streak_length
    FROM GroupedStreaks
    GROUP BY user_id, streak_group
) StreakLengths
WHERE streak_length >= 30;
