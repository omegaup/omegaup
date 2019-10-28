# Take just one of the duplicated elements as the one that will remain id DB.
# Update the relations with Courses
UPDATE
    `Courses`
INNER JOIN (
    SELECT
        `sc`.`school_id`, `min_id`
    FROM
        `Schools` `sc`
    INNER JOIN (
        SELECT
            `name`, min(`school_id`) as `min_id`
        FROM
            `Schools`
        GROUP BY
            `name`
        HAVING
            COUNT(*) > 1
    ) `dups` # duplicates
    ON `sc`.`name` = `dups`.`name`
) `upd` # updatable
ON
    `Courses`.`school_id` = `upd`.`school_id`
SET
    `Courses`.`school_id` = `upd`.`min_id`;

# Update the relations with User_Rank
UPDATE
    `User_Rank`
INNER JOIN (
    SELECT
        `sc`.`school_id`, `min_id`
    FROM
        `Schools` `sc`
    INNER JOIN (
        SELECT
            `name`, min(`school_id`) as `min_id`
        FROM
            `Schools`
        GROUP BY
            `name`
        HAVING
            COUNT(*) > 1
    ) `dups` # duplicates
    ON `sc`.`name` = `dups`.`name`
) `upd` # updatable
ON
    `User_Rank`.`school_id` = `upd`.`school_id`
SET
    `User_Rank`.`school_id` = `upd`.`min_id`;

# Update the relations with User_Rank
UPDATE
    `Identities`
INNER JOIN (
    SELECT
        `sc`.`school_id`, `min_id`
    FROM
        `Schools` `sc`
    INNER JOIN (
        SELECT
            `name`, min(`school_id`) as `min_id`
        FROM
            `Schools`
        GROUP BY
            `name`
        HAVING
            COUNT(*) > 1
    ) `dups` # duplicates
    ON `sc`.`name` = `dups`.`name`
) `upd` # updatable
ON
    `Identities`.`school_id` = `upd`.`school_id`
SET
    `Identities`.`school_id` = `upd`.`min_id`;

# Now remove all the Schools that have no relations with any tables (unused schools).
DELETE FROM
    `Schools`
WHERE
    `school_id` NOT IN (
        SELECT DISTINCT `school_id`
        FROM `Identities`
        UNION
        SELECT DISTINCT `school_id`
        FROM `Courses`
        UNION
        SELECT DISTINCT `school_id`
        FROM `User_Rank`
    );