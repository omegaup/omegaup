-- Add rank column to coder of the month
-- Rank here implies rank of user for a particular time perod, not the same thing as user's global rank
ALTER TABLE Coder_Of_The_Month ADD COLUMN rank int(11) NOT NULL;

-- Populate previous top coder of the month rank with 1
UPDATE Coder_Of_The_Month SET rank=1;
