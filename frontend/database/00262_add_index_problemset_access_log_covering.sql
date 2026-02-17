-- Add covering index to optimize Problemset_Access_Log getAll() query
-- The index includes all columns selected by ProblemsetAccessLog::getAll()
-- enabling an index-only scan and supporting the default ORDER BY problemset_id
-- Used by: ContestDetailsTest::testDetailsUsingToken
CREATE INDEX idx_problemset_access_log_covering
  ON `Problemset_Access_Log` (`problemset_id`, `identity_id`, `ip`, `time`);
