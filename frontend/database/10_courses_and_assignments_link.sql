ALTER TABLE `ACLs`
  CHANGE COLUMN `acl_id` `acl_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Courses`
  ADD COLUMN `id_group` int(11);

ALTER TABLE `Courses`
  ADD COLUMN `id_acl` int(11),
  ADD CONSTRAINT `fk_cg_id_student_group` FOREIGN KEY (`id_group`)
    REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  DROP FOREIGN KEY `fk_cg_group_id`,
  ADD CONSTRAINT `fk_ca_id_acl` FOREIGN KEY (`id_acl`)
    REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Assignments`
  ADD UNIQUE KEY `assignment_alias` (`id_course`, `alias`),
  CHANGE COLUMN `assignement_id` `assignment_id` int(11) NOT NULL,
  CHANGE COLUMN `assignment_id` `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

