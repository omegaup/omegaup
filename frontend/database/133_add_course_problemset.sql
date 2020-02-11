-- Problemsets
ALTER TABLE `Problemsets`
    ADD COLUMN `course_id` int(11) DEFAULT NULL COMMENT 'Id del curso',
    MODIFY COLUMN `assignment_id` int(11) DEFAULT NULL COMMENT 'Id de la tarea o examen del curso',
    MODIFY COLUMN `scoreboard_url` varchar(30) DEFAULT NULL COMMENT 'Token para la url del scoreboard en problemsets',
    MODIFY COLUMN `scoreboard_url_admin` varchar(30) DEFAULT NULL COMMENT 'Token para la url del scoreboard de admin en problemsets',
    MODIFY COLUMN `type` enum('Contest','Assignment','Interview','Course') NOT NULL DEFAULT 'Contest' COMMENT 'Almacena el tipo de problemset que se ha creado',
    CHANGE `access_mode` `admission_mode` enum('private','public','registration') NOT NULL DEFAULT 'private' COMMENT 'La modalidad de acceso a este conjunto de problemas',
    ADD CONSTRAINT UNIQUE (`problemset_id`, `contest_id`, `course_id`, `assignment_id`, `interview_id`),
    ADD CONSTRAINT CHECK (`contest_id` IS NOT NULL OR `course_id` IS NOT NULL OR `assignment_id` IS NOT NULL OR `interview_id` IS NOT NULL),
    ADD CONSTRAINT `fk_psc_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Adding the courses in Problemsets table
INSERT INTO
    `Problemsets` (`acl_id`, `admission_mode`, `needs_basic_information`, `requests_user_information`, `type`, `course_id`)
SELECT
    `acl_id`,
    IF(`public` = '1', 'public', 'private'),
    `needs_basic_information`,
    `requests_user_information`,
    'Course',
    `course_id`
FROM
    `Courses`;

-- Updating Contest admission_mode in Problemsets table
UPDATE
    `Problemsets`
INNER JOIN
    `Contests`
ON
    `Contests`.`problemset_id` = `Problemsets`.`problemset_id`
SET
    `Problemsets`.`admission_mode` = `Contests`.`admission_mode`
