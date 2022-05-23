UPDATE `Contests` SET `score_mode` = 'partial' WHERE `partial_score` = 1;
UPDATE `Contests` SET `score_mode` = 'all_or_nothing' WHERE `partial_score` = 0;