ALTER TABLE
    `User_Rank`
ADD COLUMN
    `author_score` double NOT NULL DEFAULT '0',
ADD COLUMN
    `author_ranking` int DEFAULT NULL;
