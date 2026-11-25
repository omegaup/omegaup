WITH HardProblems AS (
  SELECT
    i.user_id,
    DATE(r.time) AS submission_date,
    p.problem_id
  FROM Runs r
  INNER JOIN Submissions s ON r.submission_id = s.submission_id
  INNER JOIN Problems p ON s.problem_id = p.problem_id
  INNER JOIN Identities i ON s.identity_id = i.identity_id
  WHERE
    r.verdict = 'AC'
    AND p.difficulty > 3.0
  GROUP BY
    i.user_id, DATE(r.time), p.problem_id
),

RankedDays AS (
  SELECT
    user_id,
    submission_date,
    DENSE_RANK() OVER (PARTITION BY user_id ORDER BY submission_date) AS day_rank
  FROM (
    SELECT DISTINCT user_id, submission_date
    FROM HardProblems
  ) AS distinct_days
),

Streaks AS (
  SELECT
    ep.user_id,
    ep.submission_date,
    rd.day_rank,
    DATE_SUB(ep.submission_date, INTERVAL rd.day_rank DAY) AS streak_key,
    ep.problem_id
  FROM HardProblems ep
  INNER JOIN RankedDays rd
    ON ep.user_id = rd.user_id
    AND ep.submission_date = rd.submission_date
),

StreakCounts AS (
  SELECT
    user_id,
    streak_key,
    COUNT(DISTINCT submission_date) AS streak_days,
    COUNT(DISTINCT problem_id) AS problems_solved
  FROM Streaks
  GROUP BY
    user_id,
    streak_key
),

QualifiedUsers AS (
  SELECT
    user_id
  FROM
    StreakCounts
  WHERE
    streak_days = 7
    AND problems_solved >= 10
)

SELECT
  user_id
FROM
  QualifiedUsers;
