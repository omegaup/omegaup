SELECT
  DISTINCT `a`.`owner_id` AS `user_id`
FROM
  `ACLs` AS `a`
INNER JOIN
  `Problems` AS `p` ON `p`.`acl_id` = `a`.`acl_id`;