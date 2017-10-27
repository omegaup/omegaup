-- Contests
ALTER TABLE `Contests`
  ADD FULLTEXT(`title`,`description`);
