-- First add the column as nullable
ALTER TABLE Contests
ADD COLUMN orden INT;

-- Update existing rows with a default value
UPDATE Contests
SET orden = 0
WHERE orden IS NULL;

-- Then make it NOT NULL
ALTER TABLE Contests
MODIFY COLUMN orden INT NOT NULL;

-- Add an index to improve query performance when ordering by this column
ALTER TABLE Contests
ADD INDEX idx_contests_orden (orden);
