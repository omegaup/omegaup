-- Add rank column to coder of the month
ALTER TABLE Coder_Of_The_Month ADD COLUMN rank int(11) NOT NULL;

-- Populate previous top coder of the month rank with 1
UPDATE TABLE Coder_Of_The_Month SET rank=1;
