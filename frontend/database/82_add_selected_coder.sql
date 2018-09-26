-- Coder_Of_The_Month
ALTER TABLE `Coder_Of_The_Month`
  ADD COLUMN `selected_by` int(11) DEFAULT NULL
    COMMENT 'Id de la identidad que seleccionó al coder.',
  ADD KEY `selected_by` (`selected_by`),
  ADD CONSTRAINT `fk_cotmi_identity_id` FOREIGN KEY (`selected_by`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Updating new column in Coder_Of_The_Month
UPDATE
  `Coder_Of_The_Month`
SET
  `selected_by` = '1'
WHERE
  `rank` = 1;
