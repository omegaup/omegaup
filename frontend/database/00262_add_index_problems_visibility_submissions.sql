-- Add composite index to optimize Problems::byIdentityType query
-- Supports ORDER BY submissions DESC with visibility filter, reducing filesort and full scan
-- Resolves slow query in DAO/Problems.php byIdentityType
CREATE INDEX idx_problems_visibility_submissions ON Problems (visibility, submissions DESC);
