ALTER TABLE `Users`
  ADD COLUMN `x_url` varchar(255) DEFAULT NULL COMMENT 'URL el perfil en X (antes Twitter)',
  ADD COLUMN `linkedin_url` varchar(255) DEFAULT NULL COMMENT 'URL de perfil en LinkedIn',
  ADD COLUMN `github_url` varchar(255) DEFAULT NULL COMMENT 'URL de perfil en GitHub';
