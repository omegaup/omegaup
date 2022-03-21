SELECT
    u.user_id
FROM
    Users u
INNER JOIN
    Identities i
ON
    i.user_id = u.user_id
WHERE
    i.username IN ("HunterXD", "Santidelatorre", "eduardo.pacheco");