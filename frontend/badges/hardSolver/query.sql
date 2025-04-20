WITH HardProblems AS (
  SELECT
    u.user_id,
    DATE(r.time) AS submission_date,
    r.submission_id,
    p.problem_id
  FROM Runs r
  JOIN Submissions s ON r.submission_id = s.submission_id
  JOIN Identities i ON s.identity_id = i.identity_id
  JOIN Users u ON i.user_id = u.user_id
  JOIN Problems p ON r.problem_id = p.problem_id
  WHERE p.difficulty > 3.0
  GROUP BY u.user_id, DATE(r.time), r.submission_id, p.problem_id
),

RankedDays AS (
  SELECT
    user_id,
    submission_date,
    problem_id,
    DENSE_RANK() OVER (PARTITION BY user_id ORDER BY submission_date) AS d_rank
  FROM HardProblems
),

Streaks AS (
  SELECT
    user_id,
    submission_date,
    problem_id,
    d_rank,
    submission_date - INTERVAL d_rank DAY AS streak_group
  FROM RankedDays
),

StreakCounts AS (
  SELECT
    user_id,
    streak_group,
    COUNT(DISTINCT problem_id) AS problems_solved,
    COUNT(DISTINCT submission_date) AS days_in_streak
  FROM Streaks
  GROUP BY user_id, streak_group
)

SELECT
  user_id,
  'Hard' AS badge
FROM StreakCounts
WHERE problems_solved >= 10 AND days_in_streak = 7;
