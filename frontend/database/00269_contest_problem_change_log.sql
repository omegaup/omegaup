-- Migration: Create Contest_Problem_Change_Log table
-- for efficient storage and retrieval of contest problem change events.

CREATE TABLE `Contest_Problem_Change_Log` (
  `change_id` int NOT NULL AUTO_INCREMENT,
  `contest_id` int NOT NULL COMMENT 'Contest where the problem change occurred',
  `problem_id` int NOT NULL COMMENT 'Problem that was changed',
  `identity_id` int NOT NULL COMMENT 'Identity of the admin who made the change (auditability)',
  `change_type` enum('added','modified','removed') NOT NULL COMMENT 'Type of change',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`change_id`),
  KEY `idx_contest_timestamp` (`contest_id`, `timestamp`),
  CONSTRAINT `fk_cpcl_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`),
  CONSTRAINT `fk_cpcl_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_cpcl_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
COMMENT='Log of changes to contest problems for auditability and historical tracking';
