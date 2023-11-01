-- Add unique keys to Certificates
ALTER TABLE `Certificates`
    ADD UNIQUE KEY `contest_identity_key` (`identity_id`, `contest_id`, `certificate_type`),
    ADD UNIQUE KEY `course_identity_key` (`identity_id`, `course_id`, `certificate_type`);
