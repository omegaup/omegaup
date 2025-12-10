-- Add the verification_id index to the Users table
ALTER TABLE `Users`
  ADD KEY `verification_id` (`verification_id`);
