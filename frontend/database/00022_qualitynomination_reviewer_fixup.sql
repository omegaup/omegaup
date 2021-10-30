-- QualityNomination_Reviewers
ALTER TABLE `QualityNomination_Reviewers`
  DROP FOREIGN KEY `fk_qnr_qualitynomination_id`;

ALTER TABLE `QualityNomination_Reviewers`
  MODIFY COLUMN `qualitynomination_id` int(11) NOT NULL,
  ADD CONSTRAINT `fk_qnr_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
