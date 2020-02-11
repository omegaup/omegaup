SELECT 
    `user_id`
FROM 
    `Identities`
where 
    `language_id` IS NOT NULL AND 
    `country_id`IS NOT NULL AND 
    `state_id` IS NOT NULL AND 
    `gender` IS NOT NULL AND 
    `current_identity_school_id` IS NOT NULL ;