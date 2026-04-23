ALTER TABLE `Groups_`
  ADD COLUMN `archived` tinyint(1) NOT NULL DEFAULT '0'
    COMMENT 'Indicates whether the group has been archived (soft delete)';
