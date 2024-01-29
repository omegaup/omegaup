
-- Add coder_of_the_month_id column in Certificates table
ALTER TABLE `Certificates`
  ADD COLUMN `coder_of_the_month_id` int DEFAULT NULL COMMENT 'Id del Coder del mes que obtuvo el certificado' AFTER `contest_id`,
  ADD KEY `coder_of_the_month_id` (`coder_of_the_month_id`),
  ADD CONSTRAINT `fk_ccotm_coder_of_the_month_id` FOREIGN KEY (`coder_of_the_month_id`) REFERENCES `Coder_Of_The_Month` (`coder_of_the_month_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
