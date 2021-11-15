DELETE
  `pt`
FROM
  `Problems_Tags` AS `pt`
INNER JOIN
  `Tags` AS `t`
ON
  `pt`.`tag_id` = `t`.`tag_id`
AND
  `t`.`name` IN (
        'problemTagKarel',
        'problemTagLanguage',
        'problemTagOutputOnly',
        'problemTagInteractive'
    );

DELETE FROM
    `Tags`
WHERE
    `name` IN (
        'problemTagKarel',
        'problemTagLanguage',
        'problemTagOutputOnly',
        'problemTagInteractive'
    );