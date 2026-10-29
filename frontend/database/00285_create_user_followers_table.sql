CREATE TABLE `Users_Followers` (
  `users_follower_id` int NOT NULL AUTO_INCREMENT,
  `follower_user_id` int NOT NULL COMMENT 'El usuario que sigue a alguien más',
  `followed_user_id` int NOT NULL COMMENT 'El usuario que es seguido',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`users_follower_id`),
  UNIQUE KEY `unique_follow_relationship` (`follower_user_id`, `followed_user_id`),
  KEY `idx_users_followers_followed_user_id` (`followed_user_id`),
  CONSTRAINT `fk_uf_follower_user_id` FOREIGN KEY (`follower_user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uf_followed_user_id` FOREIGN KEY (`followed_user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_uf_no_self_follow` CHECK (`follower_user_id` <> `followed_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Relaciones de seguimiento entre usuarios para el sistema de amigos/seguidos';
