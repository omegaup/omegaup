-- Add indexes to optimize course progress queries in Courses::getEnrolledAndFinishedCoursesForTabs()
ALTER TABLE `Submissions`
  ADD KEY `idx_submissions_identity_problemset_problem` (`identity_id`, `problemset_id`, `problem_id`);

ALTER TABLE `Assignments`
  ADD KEY `idx_assignments_problemset_assignment_course` (`problemset_id`, `assignment_id`, `course_id`);
