-- Add index to User_Rank table on author_score and author_ranking column
CREATE INDEX idx_user_rank_author_score_ranking ON User_Rank(author_score, author_ranking);
