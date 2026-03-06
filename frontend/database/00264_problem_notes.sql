CREATE TABLE `Problem_Notes` (
  `identity_id` int NOT NULL,
  `problem_id` int NOT NULL,
  `note_text` TEXT NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`identity_id`, `problem_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_pn_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pn_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Private notes that users save on problems';
