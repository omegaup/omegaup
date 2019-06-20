SELECT
  DISTINCT `a`.`owner_id` AS `user_id`
FROM
  `Contests` AS `c`
INNER JOIN
  `ACLs` AS `a` ON `c`.`acl_id` = `a`.`acl_id`
WHERE
  `c`.`rerun_id` != 0;