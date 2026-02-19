SELECT
  `activity`.`user_id`
FROM
  (
    (
      SELECT DISTINCT
        `a`.`owner_id` AS `user_id`,
        YEAR(`c`.`last_updated`) AS `year`
      FROM
        `Contests` AS `c`
      INNER JOIN
        `ACLs` AS `a` ON `a`.`acl_id` = `c`.`acl_id`
    )
    UNION DISTINCT
    (
      SELECT DISTINCT
        `a`.`owner_id` AS `user_id`,
        YEAR(`p`.`creation_date`) AS `year`
      FROM
        `Problems` AS `p`
      INNER JOIN
        `ACLs` AS `a` ON `p`.`acl_id` = `a`.`acl_id`
    )
    UNION DISTINCT
    (
      SELECT DISTINCT
        `u`.`user_id`,
        YEAR(`s`.`time`) AS `year`
      FROM
        `Submissions` AS `s`
      INNER JOIN
        `Users` AS `u` ON `u`.`main_identity_id` = `s`.`identity_id`
      WHERE
        `s`.`verdict` = 'AC'
    )
  ) AS `activity`
WHERE
  `activity`.`year` >= YEAR(NOW()) - 2
GROUP BY
  `activity`.`user_id`
HAVING
  COUNT(`activity`.`year`) = 3;
