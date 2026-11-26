-- Courses::countCourses() filters only on start_time.
-- Admin::apiPlatformReportStats uses that for the platform report.
ALTER TABLE `Courses`
    ADD INDEX `idx_courses_start_time` (`start_time`);
