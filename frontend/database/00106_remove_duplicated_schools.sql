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
            `name`, MIN(`school_id`) as `min_id`
        FROM
            `Schools`
        GROUP BY
            `name`, `country_id`, `state_id`
        HAVING
            COUNT(*) > 1
    ) `duplicates`
    ON `sc`.`name` = `duplicates`.`name`
) `updatables`
ON
    `Courses`.`school_id` = `updatables`.`school_id`
SET
    `Courses`.`school_id` = `updatables`.`min_id`;

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
            `name`, MIN(`school_id`) as `min_id`
        FROM
            `Schools`
        GROUP BY
            `name`, `country_id`, `state_id`
        HAVING
            COUNT(*) > 1
    ) `duplicates`
    ON `sc`.`name` = `duplicates`.`name`
) `updatables`
ON
    `User_Rank`.`school_id` = `updatables`.`school_id`
SET
    `User_Rank`.`school_id` = `updatables`.`min_id`;

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
            `name`, MIN(`school_id`) as `min_id`
        FROM
            `Schools`
        GROUP BY
            `name`, `country_id`, `state_id`
        HAVING
            COUNT(*) > 1
    ) `duplicates`
    ON `sc`.`name` = `duplicates`.`name`
) `updatables`
ON
    `Identities`.`school_id` = `updatables`.`school_id`
SET
    `Identities`.`school_id` = `updatables`.`min_id`;

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

# Add UNIQUE index to Schools
ALTER TABLE `Schools`
    ADD UNIQUE KEY `name_country_id_state_id` (`name`, `country_id`, `state_id`);