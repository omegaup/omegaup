ALTER TABLE `Schools`
ADD COLUMN `distinct_users` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `distinct_problems` int(11) NOT NULL DEFAULT 0;
