-- Certificates
ALTER TABLE `Certificates`
  ADD COLUMN `contest_place` int(11) DEFAULT NULL
    COMMENT 'Se guarda el lugar en el que quedo un estudiante si es menor o igual a certificate_cutoff';