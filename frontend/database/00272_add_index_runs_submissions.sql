-- Will optimize join of runs with submissions on verdict
ALTER TABLE `Runs`
    ADD INDEX `idx_runs_run_id_verdict` (`run_id`, `verdict`);

-- Subquery first_ac
ALTER TABLE `Submissions`
    ADD INDEX `idx_submissions_time_verdict` (`time`, `verdict`);