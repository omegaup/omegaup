CREATE TABLE `Problem_Bookmarks` (
  `identity_id` int NOT NULL,
  `problem_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`identity_id`, `problem_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_pb_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pb_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Problemas que las identidades marcaron como bookmark';