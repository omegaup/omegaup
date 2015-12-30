CREATE TABLE IF NOT EXISTS `Group_Roles` (
	`group_id` int(11) NOT NULL,
	`role_id` int(11) NOT NULL,
	`contest_id` int(11) NOT NULL DEFAULT 1,
	PRIMARY KEY (`group_id`,`role_id`,`contest_id`),
	KEY `group_id` (`group_id`),
	KEY `role_id` (`role_id`),
	KEY `contest_id` (`contest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Establece los roles que se pueden dar a los grupos.';

ALTER TABLE `Group_Roles`
	ADD CONSTRAINT `fk_gr_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT `fk_gr_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
