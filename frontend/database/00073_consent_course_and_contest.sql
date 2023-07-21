-- PrivacyStatements table

ALTER TABLE `PrivacyStatements`
  MODIFY COLUMN `type` enum('privacy_policy','contest_optional_consent','contest_required_consent','course_optional_consent','course_required_consent') NOT NULL DEFAULT 'privacy_policy' COMMENT 'Tipo de documento de privacidad';

-- Problemset_Identities table

ALTER TABLE `Problemset_Identities`
  ADD COLUMN `privacystatement_consent_id` int(11) DEFAULT NULL COMMENT 'Id del documento con el consentimiento de privacidad';

-- Groups_Identities table

ALTER TABLE `Groups_Identities`
  ADD COLUMN `privacystatement_consent_id` int(11) DEFAULT NULL COMMENT 'Id del documento con el consentimiento de privacidad';

-- PrivacyStatement_Consent_Log table

ALTER TABLE `PrivacyStatement_Consent_Log`
  ADD COLUMN `privacystatement_consent_id` int(11) DEFAULT NULL COMMENT 'Id del consentimiento de privacidad almacenado en el log' FIRST,
  DROP INDEX `identity_privacy`,
  DROP FOREIGN KEY `fk_pci_identity_id`;

-- Filling PrivacyStatements table with request data

INSERT INTO
  `PrivacyStatements` (`git_object_id`, `type`)
VALUES
  ('534d173d57e3814174ac02cc25f92e4253829d9c', 'contest_optional_consent'),
  ('91e633b9d9dff264f3f6d177722d1cec581ef3b6', 'contest_required_consent'),
  ('9a5d59f71386c5132d366977eb58c1956e86b8bc', 'course_optional_consent'),
  ('5f9b5aba517a31ec05e637c2d1770dec84a71cc6', 'course_required_consent');

-- Gets information of Groups_Identities table and inserts into PrivacyStatement_Consent_Log

DELIMITER $$
CREATE PROCEDURE `Groups_Identities_Privacy`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE privacystatementconsentid, groupid, identityid INT(11);
  DECLARE requestsuserinformation VARCHAR(8);
  DECLARE cur CURSOR FOR SELECT `Groups_Identities`.`group_id`, `identity_id`, `requests_user_information` FROM `Groups_Identities` INNER JOIN `Courses` ON `Courses`.`group_id` = `Groups_Identities`.`group_id` WHERE `requests_user_information` <> 'no';

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO groupid, identityid, requestsuserinformation;
    IF done THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO `PrivacyStatement_Consent_Log` (`identity_id`, `privacystatement_id`)
    VALUES (
    identityid,
    (SELECT
       `privacystatement_id`
     FROM
       `PrivacyStatements`
     WHERE
       `type` = CONCAT('course_', requestsuserinformation, '_consent')
     ORDER BY
       `privacystatement_id`
     DESC LIMIT 1));

    SET privacystatementconsentid = LAST_INSERT_ID();

    UPDATE
      `Groups_Identities`
    SET
      `privacystatement_consent_id` = privacystatementconsentid
    WHERE
      `group_id` = groupid AND `identity_id`= identity_id;
  END LOOP read_loop;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Groups_Identities_Privacy`();

DROP PROCEDURE `Groups_Identities_Privacy`;

-- Gets information of Problemset_Identities table and inserts into PrivacyStatement_Consent_Log

DELIMITER $$
CREATE PROCEDURE `Problemsets_Identities_Privacy`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE privacystatementconsentid, problemsetid, identityid INT(11);
  DECLARE requestsuserinformation VARCHAR(8);
  DECLARE cur CURSOR FOR SELECT `Problemset_Identities`.`problemset_id`, `identity_id`, `requests_user_information` FROM `Problemset_Identities` INNER JOIN `Problemsets` ON `Problemsets`.`problemset_id` = `Problemset_Identities`.`problemset_id` WHERE `requests_user_information` <> 'no';

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO problemsetid, identityid, requestsuserinformation;
    IF done THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO `PrivacyStatement_Consent_Log` (`identity_id`, `privacystatement_id`)
    VALUES (
    identityid,
    (SELECT
       `privacystatement_id`
     FROM
       `PrivacyStatements`
     WHERE
       `type` = CONCAT('course_', requestsuserinformation, '_consent')
     ORDER BY
       `privacystatement_id`
     DESC LIMIT 1));

    SET privacystatementconsentid = LAST_INSERT_ID();

    UPDATE
      `Problemset_Identities`
    SET
      `privacystatement_consent_id` = privacystatementconsentid
    WHERE
      `problemset_id` = problemsetid AND `identity_id`= identity_id;
  END LOOP read_loop;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Problemsets_Identities_Privacy`();

DROP PROCEDURE `Problemsets_Identities_Privacy`;

-- PrivacyStatement_Consent_Log table

ALTER TABLE `PrivacyStatement_Consent_Log`
  MODIFY COLUMN `privacystatement_consent_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id del consentimiento de privacidad almacenado en el log',
  DROP PRIMARY KEY,
  ADD PRIMARY KEY (`privacystatement_consent_id`),
  ADD UNIQUE KEY `identity_privacy` (`identity_id`, `privacystatement_id`),
  ADD CONSTRAINT `fk_pci_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Problemset_Identities table

ALTER TABLE `Problemset_Identities`
  ADD CONSTRAINT `fk_pipc_privacystatement_consent_id` FOREIGN KEY (`privacystatement_consent_id`) REFERENCES `PrivacyStatement_Consent_Log` (`privacystatement_consent_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Groups_Identities table

ALTER TABLE `Groups_Identities`
  ADD CONSTRAINT `fk_gipc_privacystatement_consent_id` FOREIGN KEY (`privacystatement_consent_id`) REFERENCES `PrivacyStatement_Consent_Log` (`privacystatement_consent_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
