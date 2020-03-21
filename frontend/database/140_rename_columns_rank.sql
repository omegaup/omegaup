ALTER TABLE `Coder_Of_The_Month`
CHANGE COLUMN `rank` `ranking` int NOT NULL COMMENT 'El lugar en el que el usuario estuvo durante ese mes' ;

ALTER TABLE `School_Of_The_Month`
CHANGE COLUMN `rank` `ranking` int NOT NULL COMMENT 'El lugar que tuvo la escuela en el mes.';

ALTER TABLE `Schools`
CHANGE COLUMN `rank` `ranking` int DEFAULT NULL;

ALTER TABLE `User_Rank`
CHANGE COLUMN `rank` `ranking` int NOT NULL;
