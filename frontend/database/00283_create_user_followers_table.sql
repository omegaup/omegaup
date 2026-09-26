CREATE TABLE `User_Connections` (
  `follower_id` int NOT NULL AUTO_INCREMENT,
  `follower_identity_id` int NOT NULL,
  `followed_identity_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`follower_id`),
  UNIQUE KEY `unique_follow_relationship` (`follower_identity_id`, `followed_identity_id`),
  KEY `idx_follower_identity_id` (`follower_identity_id`),
  KEY `idx_followed_identity_id` (`followed_identity_id`),
  CONSTRAINT `fk_follower_identity_id` FOREIGN KEY (`follower_identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_followed_identity_id` FOREIGN KEY (`followed_identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Stores user follow relationships for the Friends/Following system';
