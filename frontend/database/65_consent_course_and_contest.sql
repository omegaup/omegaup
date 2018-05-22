-- PrivacyStatements table

ALTER TABLE `PrivacyStatements`
  MODIFY COLUMN `type` enum('privacy_policy','contest_optional_consent','contest_required_consent','course_optional_consent','course_required_consent') NOT NULL DEFAULT 'privacy_policy' COMMENT 'Tipo de documento de privacidad';

-- PrivacyStatement_Consent_Log table

ALTER TABLE `PrivacyStatement_Consent_Log`
  ADD COLUMN `acl_id` int(11) DEFAULT NULL COMMENT 'Id de la lista de acceso al que pertenece el usuario que acepta/deniega el consentimiento de compartir sus datos. Se obtiene si es un curso' AFTER `privacystatement_id`,
  ADD COLUMN `share_user_information` tinyint(1) DEFAULT NULL COMMENT 'Almacena la respuesta del participante de un concurso / curso si está de acuerdo en divulgar su información.' AFTER `acl_id`,
  ADD CONSTRAINT `fk_pcla_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Filling PrivacyStatements table with request data

INSERT INTO
  `PrivacyStatements` (`git_object_id`, `type`)
VALUES
  ('CONTEST-OPTIONAL-HEAD', 'contest_optional_consent'),
  ('CONTEST-REQUIRED-HEAD', 'contest_required_consent'),
  ('COURSE-OPTIONAL-HEAD', 'course_optional_consent'),
  ('COURSE-REQUIRED-HEAD', 'course_required_consent'); # There is nothing to get from GIT 

-- Gets information of Groups_Identities table and inserts into PrivacyStatement_Consent_Log

INSERT INTO 
  `PrivacyStatement_Consent_Log` (`identity_id`, `privacystatement_id`, `acl_id`, `share_user_information`)
  SELECT
    `identity_id`,
    (SELECT
       `privacystatement_id`
     FROM
       `PrivacyStatements`
     WHERE
       `type` = CONCAT('course_', c.requests_user_information, '_consent')
     ORDER BY
       `privacystatement_id`
     DESC LIMIT 1) AS `privacystatement_id`,
    `acl_id`,
    `share_user_information`
  FROM
    `Groups_Identities` gi
  INNER JOIN
    `Courses` c
  ON
    gi.group_id = c.group_id
  WHERE
    gi.share_user_information IS NOT NULL;

-- Gets information of Problemset_Identities table and inserts into PrivacyStatement_Consent_Log

INSERT INTO 
  `PrivacyStatement_Consent_Log` (`identity_id`, `privacystatement_id`, `acl_id`, `share_user_information`)
  SELECT
    `identity_id`,
    (SELECT
       `privacystatement_id`
     FROM
       `PrivacyStatements`
     WHERE
       `type` = CONCAT('contest_', p.requests_user_information, '_consent')
     ORDER BY
       `privacystatement_id`
     DESC LIMIT 1) AS `privacystatement_id`,
    `p`.`acl_id`,
    `share_user_information`
  FROM
    `Problemset_Identities` pi
  INNER JOIN
    `Problemsets` p
  ON
    pi.problemset_id = p.problemset_id
  INNER JOIN
    `Contests` c
  ON
    pi.problemset_id = c.problemset_id
  WHERE
    pi.share_user_information IS NOT NULL;

-- Dropping column share_user_information in Groups_Identities and Problemset_Identities

ALTER TABLE `Problemset_Identities` DROP COLUMN `share_user_information`;
ALTER TABLE `Groups_Identities` DROP COLUMN `share_user_information`;
