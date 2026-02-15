-- Add composite index to optimize problem list/count queries
-- The byIdentityType() query filters by both visibility and difficulty range.
-- This index allows MySQL to use index range scan instead of full table scan
-- when both visibility and difficulty conditions are applied.
CREATE INDEX idx_problems_visibility_difficulty ON Problems (visibility, difficulty);
