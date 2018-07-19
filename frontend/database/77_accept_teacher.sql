-- Groups_Users: consent for teacher
ALTER TABLE `Groups_Identities`
  ADD COLUMN `accept_teacher` enum('yes','no') DEFAULT NULL
    COMMENT 'Almacena la respuesta del participante de un curso si acepta al organizador como su maestro.';

-- PrivacyStatements table
ALTER TABLE `PrivacyStatements`
  MODIFY COLUMN `type` enum('privacy_policy','contest_optional_consent','contest_required_consent','course_optional_consent','course_required_consent','accept_teacher') NOT NULL DEFAULT 'privacy_policy' COMMENT 'Tipo de documento de privacidad';
