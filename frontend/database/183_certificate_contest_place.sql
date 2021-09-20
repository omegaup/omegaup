-- Certificate
ALTER TABLE `Certificates`
  ADD COLUMN `contest_place` int(11) DEFAULT NULL
    COMMENT 'Si el tipo de certificado es CONTEST y el lugar en el que quedo el estudiante es menor o igual a `certificate_cutoff` entonces en este campo se guarda el lugar en el que quedó. Si no, NULL.';