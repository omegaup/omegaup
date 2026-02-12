CREATE TABLE `GSoC_Edition` (
  `edition_id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `application_deadline` timestamp NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`edition_id`),
  UNIQUE KEY `unique_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Google Summer of Code editions';

CREATE TABLE `GSoC_Idea` (
  `idea_id` int NOT NULL AUTO_INCREMENT,
  `edition_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `brief_description` text,
  `expected_results` text,
  `preferred_skills` text,
  `possible_mentors` text,
  `estimated_hours` int DEFAULT NULL,
  `skill_level` enum('Low', 'Medium', 'Advanced') DEFAULT NULL,
  `status` enum('Proposed', 'Accepted', 'Archived') DEFAULT 'Proposed',
  `blog_link` varchar(500) DEFAULT NULL,
  `contributor_username` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idea_id`),
  KEY `fk_edition` (`edition_id`),
  CONSTRAINT `fk_gsoc_idea_edition` FOREIGN KEY (`edition_id`) REFERENCES `GSoC_Edition` (`edition_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Google Summer of Code project ideas';
