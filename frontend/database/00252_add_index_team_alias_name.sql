-- Add index to Team_Groups table on alias, name column
CREATE INDEX idx_team_groups_alias_name ON Team_Groups (alias, name);
