SELECT
    `u`.`user_id`
FROM
    `Users` AS `u`
GROUP BY
	`u`.`user_id`
HAVING
(SELECT
    COUNT(DISTINCT `p`.`problem_id`)
FROM
    `Problems` AS `p`
INNER JOIN
    `Submissions` AS `s` ON `p`.`problem_id` = `s`.`problem_id`
INNER JOIN
    `Runs` AS `r` ON `r`.`run_id` = `s`.`current_run_id`
INNER JOIN
    `Identities` AS `i` ON `s`.`identity_id` = `i`.`identity_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`main_identity_id` = `i`.`identity_id`
WHERE
    `r`.`verdict` = "AC" AND
    YEAR(`s`.`time`) = YEAR(NOW())
    AND `u2`.`user_id` = `u`.`user_id`
 ) >= 1 OR
(SELECT
    COUNT(DISTINCT `p`.`problem_id`)
FROM
    `Problems` AS `p`
INNER JOIN
	`ACLs` AS `a` ON `a`.`acl_id` = `p`.`acl_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`user_id` = `a`.`owner_id`
WHERE
	`u2`.`user_id` = `u`.`user_id` AND
	YEAR(`p`.`creation_date`) = YEAR(NOW())) >= 1 OR
(SELECT
    COUNT(DISTINCT `c`.`contest_id`)
FROM
    `Contests` AS `c`
INNER JOIN
	`ACLs` AS `a` ON `a`.`acl_id`=`c`.`acl_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`user_id` = `a`.`owner_id`
WHERE `u2`.`user_id`=`u`.`user_id` AND
    YEAR(`c`.`last_updated`) = YEAR(NOW())) AND (SELECT
    COUNT(DISTINCT `p`.`problem_id`)
FROM
    `Problems` AS `p`
INNER JOIN
    `Submissions` AS `s` ON `p`.`problem_id` = `s`.`problem_id`
INNER JOIN
    `Runs` AS `r` ON `r`.`run_id` = `s`.`current_run_id`
INNER JOIN
    `Identities` AS `i` ON `s`.`identity_id` = `i`.`identity_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`main_identity_id` = `i`.`identity_id`
WHERE
    (`r`.`verdict`= "AC" ) AND
    YEAR(`s`.`time`) = YEAR(NOW())-1
    AND `u2`.`user_id` = `u`.`user_id`
 )>=1 OR
 (SELECT
    COUNT(DISTINCT `p`.`problem_id`)
FROM
    `Problems` AS `p`
INNER JOIN
	`ACLs` AS `a` ON `a`.`acl_id` = `p`.`acl_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`user_id` = `a`.`owner_id`
WHERE
    `u2`.`user_id` = `u`.`user_id` AND
    YEAR(`p`.`creation_date`) = YEAR(NOW())-1) >= 1 OR
(SELECT
    COUNT(DISTINCT `c`.`contest_id`)
FROM
    `Contests` AS `c`
INNER JOIN
	`ACLs` AS `a` ON `a`.`acl_id` = `c`.`acl_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`user_id` = `a`.`owner_id`
    where `u2`.`user_id` = `u`.`user_id` AND
    YEAR(`c`.`last_updated`) = YEAR(NOW())-1) AND
(SELECT
    COUNT(DISTINCT `p`.`problem_id`)
FROM
    `Problems` AS `p`
INNER JOIN
    `Submissions` AS `s` ON `p`.`problem_id` = `s`.`problem_id`
INNER JOIN
    `Runs` AS `r` ON `r`.`run_id` = `s`.`current_run_id`
INNER JOIN
    `Identities` AS `i` ON `s`.`identity_id` = `i`.`identity_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`main_identity_id` = `i`.`identity_id`
WHERE
    (`r`.`verdict`= "AC" ) AND
    YEAR(`s`.`time`) = YEAR(NOW())-2
    AND `u2`.`user_id` = `u`.`user_id`
 )>=1 OR
 (SELECT
    COUNT(DISTINCT `p`.`problem_id`)
FROM
    `Problems` AS `p`
INNER JOIN
	`ACLs` AS `a` ON `a`.`acl_id` = `p`.`acl_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`user_id` = `a`.`owner_id`
where `u2`.`user_id` = `u`.`user_id` AND
YEAR(`p`.`creation_date`) = YEAR(NOW())-2) >= 1 OR
(SELECT
    COUNT(DISTINCT `c`.`contest_id`)
FROM
    `Contests` AS `c`
INNER JOIN
	`ACLs` AS `a` ON `a`.`acl_id` = `c`.`acl_id`
INNER JOIN
    `Users` AS `u2` ON `u2`.`user_id` = `a`.`owner_id`
    where `u2`.`user_id` = `u`.`user_id` AND
    YEAR(`c`.`last_updated`) = YEAR(NOW())-2 >=1 );