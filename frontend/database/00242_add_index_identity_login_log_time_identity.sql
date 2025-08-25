-- Add index to indetity_login_log table on time and id_identity column
CREATE INDEX idx_loginlog_time_identity ON Identity_Login_Log (time, identity_id);