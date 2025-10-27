-- Add index to Problems table on quality, acl_id column
CREATE INDEX idx_problems_quality_acl ON Problems (quality, acl_id);
