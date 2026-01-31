-- Merge duplicate Universidad Fidélitas school profiles (#8417)
START TRANSACTION;

SET @target_school_id = 11741; -- Official Universidad Fidélitas
SET @duplicate_ids = '10230,11464,11479,11499,11522,11530,11586,11727,11732,11745,11747,11779,11805,11862,11904,12028,12041,12048';

-- Repoint all FK references to the official school
UPDATE `Identities_Schools` SET `school_id` = @target_school_id WHERE FIND_IN_SET(`school_id`, @duplicate_ids);
UPDATE `Courses` SET `school_id` = @target_school_id WHERE FIND_IN_SET(`school_id`, @duplicate_ids);
UPDATE `User_Rank` SET `school_id` = @target_school_id WHERE FIND_IN_SET(`school_id`, @duplicate_ids);
UPDATE `Submissions` SET `school_id` = @target_school_id WHERE FIND_IN_SET(`school_id`, @duplicate_ids);
UPDATE `Coder_Of_The_Month` SET `school_id` = @target_school_id WHERE FIND_IN_SET(`school_id`, @duplicate_ids);
UPDATE `School_Of_The_Month` SET `school_id` = @target_school_id WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

-- Delete old Schools_Problems_Solved_Per_Month entries
DELETE FROM `Schools_Problems_Solved_Per_Month` WHERE FIND_IN_SET(`school_id`, @duplicate_ids);

-- Delete duplicates if no FK references
DELETE FROM `Schools`
WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
AND `school_id` NOT IN (
    SELECT DISTINCT `school_id` FROM `Identities_Schools` WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
    UNION
    SELECT DISTINCT `school_id` FROM `Courses` WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
    UNION
    SELECT DISTINCT `school_id` FROM `User_Rank` WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
    UNION
    SELECT DISTINCT `school_id` FROM `Submissions` WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
    UNION
    SELECT DISTINCT `school_id` FROM `Coder_Of_The_Month` WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
    UNION
    SELECT DISTINCT `school_id` FROM `School_Of_The_Month` WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
    UNION
    SELECT DISTINCT `school_id` FROM `Schools_Problems_Solved_Per_Month` WHERE FIND_IN_SET(`school_id`, @duplicate_ids)
);

COMMIT;
