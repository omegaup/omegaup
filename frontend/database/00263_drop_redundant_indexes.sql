-- Drop redundant indexes that are prefixes of composite primary keys,
-- exact duplicates of single-column primary keys, or duplicate indexes
-- on the same column with different names.

-- Prefix of PK (user_id, problem_id)
ALTER TABLE `Favorites` DROP INDEX `user_id`;

-- Prefix of PK (problemset_id, problem_id)
ALTER TABLE `Problemset_Problems` DROP INDEX `problemset_id`;

-- Prefix of PK (problem_id, tag_id)
ALTER TABLE `Problems_Tags` DROP INDEX `problem_id`;

-- Prefix of PK (problem_id, identity_id)
ALTER TABLE `Problem_Viewed` DROP INDEX `problem_id`;

-- Prefix of PK (user_id, problem_id)
ALTER TABLE `Problems_Forfeited` DROP INDEX `user_id`;

-- Prefix of PK (problem_id, language_id)
ALTER TABLE `Problems_Languages` DROP INDEX `problem_id`;

-- Exact duplicate of single-column PK
ALTER TABLE `Coder_Of_The_Month` DROP INDEX `coder_of_the_month_id`;

-- Exact duplicate of single-column PK
ALTER TABLE `School_Of_The_Month` DROP INDEX `school_of_the_month_id`;

-- Duplicate index on identity_id (same column as existing KEY identity_id)
ALTER TABLE `Auth_Tokens` DROP INDEX `acting_identity_id`;
