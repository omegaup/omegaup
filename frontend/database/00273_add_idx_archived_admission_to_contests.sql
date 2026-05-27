-- Index useful for filtering contests by archived and admission mode,
-- which is used in the list of contests and in the contest details page.
ALTER TABLE `Contests`
    ADD INDEX `idx_archived_admission` (`archived`, `admission_mode`);
