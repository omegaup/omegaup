-- Problems
ALTER TABLE `Problems`
	ADD COLUMN `commit` char(40) NOT NULL DEFAULT 'published' COMMENT 'El hash SHA1 del commit en la rama master del problema.' AFTER `alias`;

-- Problemset_Problems
ALTER TABLE `Problemset_Problems`
	ADD COLUMN `commit` char(40) NOT NULL DEFAULT 'published' COMMENT 'El hash SHA1 del commit en la rama master del problema.' AFTER `problem_id`;
