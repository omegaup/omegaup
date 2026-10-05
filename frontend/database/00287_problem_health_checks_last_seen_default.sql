-- Problem_Health_Checks.last_seen_at is NOT NULL but had no default, so
-- inserting a VO that does not set it explicitly failed. Give it the same
-- CURRENT_TIMESTAMP default that first_detected_at already has.
ALTER TABLE `Problem_Health_Checks`
  MODIFY COLUMN `last_seen_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'La última ejecución en la que se seguía detectando';
