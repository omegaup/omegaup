-- Add indexes to optimize course/assignment statistics and progress queries.
-- Addresses slow queries in:
--   - Assignments::getAssignmentsProblemsStatistics
--   - Courses::getStudentsProgressPerAssignment
--   - Courses::getSingleStudentProgressPerAssignment
--   - Courses::getByProblemsetId

-- Assignments: support filter by course_id and assignment_type (exclude lessons)
CREATE INDEX idx_assignments_course_type ON Assignments (course_id, assignment_type);

-- Assignments: covering index for course problems subquery (course_id -> problemset_id)
CREATE INDEX idx_assignments_course_problemset ON Assignments (course_id, problemset_id);

-- Assignments: covering index for Courses.getByProblemsetId (problemset_id -> course_id join)
CREATE INDEX idx_assignments_problemset_course ON Assignments (problemset_id, course_id);

-- Submissions: support LEFT JOIN lookups by (identity_id, problemset_id, problem_id)
-- Used when joining from group_members × course_problems to find user submissions
CREATE INDEX idx_submissions_identity_problemset_problem
  ON Submissions (identity_id, problemset_id, problem_id);
