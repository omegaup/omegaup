-- Add index to Users table on verified, in_mailing_list column
CREATE INDEX idx_users_verified_mailing_list ON Users (verified, in_mailing_list);
