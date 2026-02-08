
START TRANSACTION;

CREATE TABLE `GSoC_Edition` (
  `edition_id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `application_deadline` timestamp NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`edition_id`),
  UNIQUE KEY `unique_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Ediciones de Google Summer of Code';


CREATE TABLE `GSoC_Idea` (
  `idea_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `brief_description` text,
  `expected_results` text,
  `preferred_skills` text,
  `possible_mentors` text,
  `estimated_hours` int DEFAULT NULL,
  `skill_level` enum('Low', 'Medium', 'Advanced') DEFAULT NULL,
  `blog_link` varchar(500) DEFAULT NULL,
  `contributor_username` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idea_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Ideas de proyectos de Google Summer of Code (independientes de edición)';


CREATE TABLE `GSoC_Idea_Edition` (
  `idea_edition_id` int NOT NULL AUTO_INCREMENT,
  `idea_id` int NOT NULL,
  `edition_id` int NOT NULL,
  `status` enum('Proposed', 'Accepted', 'Archived') DEFAULT 'Proposed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idea_edition_id`),
  UNIQUE KEY `unique_idea_edition` (`idea_id`, `edition_id`),
  KEY `fk_idea` (`idea_id`),
  KEY `fk_edition` (`edition_id`),
  CONSTRAINT `fk_gsoc_idea_edition_idea` FOREIGN KEY (`idea_id`) REFERENCES `GSoC_Idea` (`idea_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gsoc_idea_edition_edition` FOREIGN KEY (`edition_id`) REFERENCES `GSoC_Edition` (`edition_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Vincula ideas de GSoC a ediciones con estado por edición';

COMMIT;
