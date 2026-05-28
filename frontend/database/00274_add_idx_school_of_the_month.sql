
-- Speeds up filtering by verdict and time range while covering key join columns for candidate selection.
ALTER TABLE `Submissions`
    ADD INDEX `idx_submissions_verdict_time_identity_problem_school`
        (`verdict`, `time`, `identity_id`, `problem_id`, `school_id`, `submission_id`);

-- Optimizes the NOT EXISTS lookup for first-AC per identity-problem.
ALTER TABLE `Submissions`
    ADD INDEX `idx_submissions_identity_problem_verdict_time_id`
        (`identity_id`, `problem_id`, `verdict`, `time`, `submission_id`);

-- Speeds up recent-winner exclusion by school and time.
ALTER TABLE `School_Of_The_Month`
    ADD INDEX `idx_sotm_school_time`
        (`school_id`, `time`);