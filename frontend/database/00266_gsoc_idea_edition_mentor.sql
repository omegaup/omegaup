START TRANSACTION;

ALTER TABLE `GSoC_Idea`
  DROP COLUMN `possible_mentors`,
  DROP COLUMN `contributor_username`;

CREATE TABLE `GSoC_Idea_Edition_Mentor` (
  `idea_edition_mentor_id` int NOT NULL AUTO_INCREMENT,
  `idea_edition_id` int NOT NULL,
  `identity_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idea_edition_mentor_id`),
  UNIQUE KEY `unique_idea_edition_mentor` (`idea_edition_id`, `identity_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `gsoc_idea_edition_mentor_idea_edition` FOREIGN KEY (`idea_edition_id`) REFERENCES `GSoC_Idea_Edition` (`idea_edition_id`) ON DELETE CASCADE,
  CONSTRAINT `gsoc_idea_edition_mentor_identity` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Mentores por idea y edición de GSoC';

COMMIT;
