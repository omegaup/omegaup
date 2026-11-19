-- Merge new duplicate Universidad Fidélitas school profiles (#8417)
START TRANSACTION;

SET @target_school_id = 11741; -- Official Universidad Fidélitas
SET @duplicate_ids = '12268,12316,12327,12361,12384,12392,12393,12408,12436,12935,12953,12954,13018';

-- Repoint all FK references to the official school.
UPDATE `Identities_Schools`
SET `school_id` = @target_school_id
WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

UPDATE `Courses`
SET `school_id` = @target_school_id
WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

UPDATE `User_Rank`
SET `school_id` = @target_school_id
WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

UPDATE `Submissions`
SET `school_id` = @target_school_id
WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

UPDATE `Coder_Of_The_Month`
SET `school_id` = @target_school_id
WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

UPDATE `School_Of_The_Month`
SET `school_id` = @target_school_id
WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

-- This table is derived and will be rebuilt by update_ranks.
DELETE FROM `Schools_Problems_Solved_Per_Month`
WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

-- Remove duplicate schools only after all references have been repointed.
DELETE FROM `Schools`
WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
AND `school_id` NOT IN (
    SELECT DISTINCT `school_id`
    FROM `Identities_Schools`
    WHERE FIND_IN_SET(`school_id`, @duplicate_ids)

    UNION

    SELECT DISTINCT `school_id`
    FROM `Courses`
    WHERE FIND_IN_SET(`school_id`, @duplicate_ids)

    UNION

    SELECT DISTINCT `school_id`
    FROM `User_Rank`
    WHERE FIND_IN_SET(`school_id`, @duplicate_ids)

    UNION

    SELECT DISTINCT `school_id`
    FROM `Submissions`
    WHERE FIND_IN_SET(`school_id`, @duplicate_ids)

    UNION

    SELECT DISTINCT `school_id`
    FROM `Coder_Of_The_Month`
    WHERE FIND_IN_SET(`school_id`, @duplicate_ids)

    UNION

    SELECT DISTINCT `school_id`
    FROM `School_Of_The_Month`
    WHERE FIND_IN_SET(`school_id`, @duplicate_ids)

    UNION

    SELECT DISTINCT `school_id`
    FROM `Schools_Problems_Solved_Per_Month`
    WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
);

COMMIT;
