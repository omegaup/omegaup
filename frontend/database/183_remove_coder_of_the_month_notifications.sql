DELETE FROM
    `Notifications`
WHERE
    JSON_EXTRACT(`contents`, '$.type') = 'coder-of-the-month';
