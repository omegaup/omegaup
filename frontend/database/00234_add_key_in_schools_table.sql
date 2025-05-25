-- Add index to the Schools table
ALTER TABLE `Schools`
  ADD KEY `idx_schools_score` (`score`);
