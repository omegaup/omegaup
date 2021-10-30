/*!80000 ALTER TABLE `Problemsets` DROP CONSTRAINT `Problemsets_chk_1` */;
ALTER TABLE `Problemsets`
  ADD CONSTRAINT CHECK (CAST(`contest_id` IS NOT NULL AS UNSIGNED) + CAST(`assignment_id` IS NOT NULL AS UNSIGNED) + CAST(`interview_id` IS NOT NULL AS UNSIGNED) <= 1);
