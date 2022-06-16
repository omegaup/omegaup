-- Temporary Drop key to Schools
ALTER TABLE `Schools`
    DROP KEY `name_country_id_state_id`;

-- Add unique key to Schools again, but with an specific length
ALTER TABLE `Schools`
    ADD UNIQUE KEY `name_country_id_state_id` (`name`(128), `country_id`(3), `state_id`(3));
