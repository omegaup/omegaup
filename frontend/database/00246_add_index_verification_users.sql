-- Add index to Users table on parental_verification_token column
CREATE INDEX idx_users_parental_verification ON Users(parental_verification_token);
