ALTER TABLE `User_Readme_Report_Log`
  ADD COLUMN `reason` varchar(255) DEFAULT NULL COMMENT 'Motivo por el cual se reportó el README',
  ADD COLUMN `detail` text DEFAULT NULL COMMENT 'Detalles adicionales sobre el reporte proporcionados por el usuario';
