ALTER TABLE
    `Courses`
CHANGE
    `start_time` `start_time` datetime NOT NULL DEFAULT '2000-01-01 06:00:00',
CHANGE
    `finish_time` `finish_time` datetime DEFAULT NULL;
