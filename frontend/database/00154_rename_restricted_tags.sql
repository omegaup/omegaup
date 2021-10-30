UPDATE
    `Tags`
SET
    `name` = CASE `name`
        WHEN 'karel'
            THEN 'problemRestrictedTagKarel'
        WHEN 'lenguaje'
            THEN 'problemRestrictedTagLanguage'
        WHEN 'solo-salida'
            THEN 'problemRestrictedTagOnlyOutput'
        WHEN 'interactive'
            THEN 'problemRestrictedTagInteractive'
        ELSE
            `name`
        END;

UPDATE
    `Tags`
SET
    `public` = 1
WHERE
    `name` IN (
        'problemRestrictedTagKarel',
        'problemRestrictedTagLanguage',
        'problemRestrictedTagOnlyOutput',
        'problemRestrictedTagInteractive'
    );
