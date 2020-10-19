-- Create an index to make querying pending runs faster.
CREATE INDEX `status_submission_id` ON `Runs` (`status`, `submission_id`);
