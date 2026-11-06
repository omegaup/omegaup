-- Add composite index on (identity_id, create_time) to optimize token cleanup
-- This helps with:
-- 1. Fast deletion of old tokens by identity
-- 2. Fast ordering by create_time when keeping only recent tokens
-- 3. Cron job that deletes tokens older than N days

ALTER TABLE `Auth_Tokens`
  ADD KEY `idx_identity_create_time` (`identity_id`, `create_time`);
