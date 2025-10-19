-- Add index to User_Rank table on author_ranking, user_id column
CREATE INDEX idx_user_rank_order ON User_Rank (author_ranking, user_id);
