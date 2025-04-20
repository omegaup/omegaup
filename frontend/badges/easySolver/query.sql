WITH EasyProblems AS (
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
  WHERE p.difficulty < 1.5
  GROUP BY u.user_id, DATE(r.time), r.submission_id, p.problem_id
),

RankedDays AS (
  SELECT
    ep.user_id,
    ep.submission_date,
    DENSE_RANK() OVER (PARTITION BY ep.user_id ORDER BY ep.submission_date) AS d_rank
  FROM EasyProblems ep
),

Streaks AS (
  SELECT
    ep.user_id,
    ep.submission_date,
    ep.problem_id,
    rd.d_rank,
    ep.submission_date - INTERVAL rd.d_rank DAY AS streak_group
  FROM EasyProblems ep
  JOIN RankedDays rd ON ep.user_id = rd.user_id AND ep.submission_date = rd.submission_date
),

StreakCounts AS (
  SELECT
    user_id,
    streak_group,
    COUNT(DISTINCT problem_id) AS easy_solved_count,
    COUNT(DISTINCT submission_date) AS streak_days
  FROM Streaks
  GROUP BY user_id, streak_group
),

QualifiedUsers AS (
  SELECT user_id
  FROM StreakCounts
  WHERE easy_solved_count >= 10 AND streak_days = 7
)

SELECT user_id, 'Easy' AS badge
FROM QualifiedUsers;
