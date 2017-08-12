-- omegaup:curators
INSERT INTO `ACLs` (`acl_id`, `owner_id`) VALUES (4, 1);

INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (
	4,
	'omegaup:curator',
	'omegaup:curator',
	'Miembros del grupo de curadores.'
);

-- Public course
ALTER TABLE `Courses`
  ADD COLUMN `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True implica cualquier usuario puede entrar al curso';
