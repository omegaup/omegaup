-- Add index to User_Rank table on score and user_id column
CREATE INDEX idx_user_rank_score_userid ON User_Rank(score, user_id);