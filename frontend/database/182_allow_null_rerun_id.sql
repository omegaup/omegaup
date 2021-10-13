ALTER TABLE `Contests`
    MODIFY COLUMN `rerun_id` int DEFAULT NULL COMMENT 'Este campo es para las repeticiones de algún concurso, Contiene el id del concurso original o null en caso de ser un concurso original.';

UPDATE `Contests` SET `rerun_id` = NULL WHERE `rerun_id` = 0;

ALTER TABLE `Contests`
    ADD CONSTRAINT `fk_cc_rerun_id` FOREIGN KEY (`rerun_id`) REFERENCES `Contests` (`contest_id`);
