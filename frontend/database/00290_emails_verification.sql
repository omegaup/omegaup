ALTER TABLE `Emails`
  ADD COLUMN `verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el usuario comprobó que tiene acceso a este correo',
  ADD COLUMN `verification_id` varchar(50) DEFAULT NULL COMMENT 'Token pendiente de verificación de este correo',
  ADD COLUMN `verification_expires_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha límite para usar el token de verificación',
  ADD KEY `verification_id` (`verification_id`);

-- 1. Main email of an already verified user -> verified.
--    The `e.user_id = u.user_id` condition skips main_email_id values that
--    point to another user's email. Missing or deleted main emails are skipped
--    by the join.
UPDATE `Emails` e
INNER JOIN `Users` u ON u.main_email_id = e.email_id AND u.user_id = e.user_id
SET e.verified = 1
WHERE u.verified = 1;

-- 2. Only users that are still unverified keep a pending token with 24h expiry.
--    Verified users (including third-party registrations that kept a non-null
--    token) do not get one.
UPDATE `Emails` e
INNER JOIN `Users` u ON u.main_email_id = e.email_id AND u.user_id = e.user_id
SET e.verification_id = u.verification_id,
    e.verification_expires_at = NOW() + INTERVAL 24 HOUR
WHERE u.verified = 0
  AND u.verification_id IS NOT NULL;
