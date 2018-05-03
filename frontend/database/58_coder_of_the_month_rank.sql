-- Add rank column to coder of the month
ALTER TABLE Coder_Of_The_Month ADD COLUMN rank int(11) NOT NULL COMMENT 'El lugar en el que el usuario estuvo durante ese mes';

-- Populate previous top coder of the month rank with 1
UPDATE Coder_Of_The_Month SET rank=1;
