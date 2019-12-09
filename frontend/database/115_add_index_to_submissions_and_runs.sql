CREATE INDEX `school_id_problem_id` ON `Submissions` (`school_id`, `problem_id`);
CREATE UNIQUE INDEX `submission_id` ON `Runs` (`submission_id`);