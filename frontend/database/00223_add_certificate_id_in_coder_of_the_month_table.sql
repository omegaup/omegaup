
-- Add certificate_id column
ALTER TABLE `Coder_Of_The_Month`
  ADD COLUMN `certificate_id` int DEFAULT NULL COMMENT 'Id del certificado que pertenece al Coder del mes',
  ADD KEY `certificate_id` (`certificate_id`),
  ADD CONSTRAINT `fk_cotmc_certificate_id` FOREIGN KEY (`certificate_id`) REFERENCES `Certificates` (`certificate_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;