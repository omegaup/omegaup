UPDATE Submissions s
INNER JOIN Runs r ON r.run_id = s.current_run_id
SET s.verdict = r.verdict, s.status = r.status;
