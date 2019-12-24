ALTER TABLE `Schools`
CHANGE `rank` `rank` int(11) DEFAULT NULL;

UPDATE `Schools`
SET `rank` = NULL
WHERE `rank` = 0;
