-- API_Tokens
ALTER TABLE `API_Tokens`
  ADD CONSTRAINT UNIQUE `user_name` (`user_id`, `name`);
