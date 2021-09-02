UPDATE `QualityNominations` AS `qn`
INNER JOIN `Problems` AS `p` ON `qn`.`problem_id` = `p`.`problem_id`
SET `qn`.`status` = 'banned'
WHERE `qn`.`nomination` = 'demotion'
AND `qn`.`status` = 'resolved'
AND (`p`.`visibility` = -3 OR `p`.`visibility` = -2);

UPDATE `QualityNominations` AS `qn`
INNER JOIN `Problems` AS `p` ON `qn`.`problem_id` = `p`.`problem_id`
SET `qn`.`status` = 'resolved'
WHERE `qn`.`nomination` = 'demotion'
AND `qn`.`status` = 'banned'
AND `p`.`visibility` >= -1;