SELECT
  DISTINCT `qn`.`user_id`
FROM
  `QualityNominations` AS `qn`
WHERE
  `qn`.`nomination` = 'suggestion';