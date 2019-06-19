SELECT
  DISTINCT `a`.`owner_id` AS `user_id`
FROM
  `Problems` AS `p`
INNER JOIN
  `ACLs` AS `a` ON `p`.`acl_id` = `a`.`acl_id`;