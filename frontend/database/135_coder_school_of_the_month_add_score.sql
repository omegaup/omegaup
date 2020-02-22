ALTER TABLE
    `Coder_Of_The_Month`
ADD COLUMN
    `score` double NOT NULL DEFAULT '0',
ADD COLUMN
    `problems_solved` int(11) NOT NULL DEFAULT '0';

ALTER TABLE
    `School_Of_The_Month`
ADD COLUMN
    `score` double NOT NULL DEFAULT '0';
