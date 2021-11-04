-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `school_id` int(11) DEFAULT NULL,
  ADD CONSTRAINT `fk_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

CREATE INDEX `school_id` ON Courses (`school_id`);
