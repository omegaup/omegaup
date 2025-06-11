-- Add index to the Courses table
ALTER TABLE `Courses`
  ADD KEY `idx_admission_mode_recommended_archived` (`admission_mode`, `recommended`, `archived`);

-- Add index to the Assignments table
ALTER TABLE `Assignments`
    ADD KEY `idx_assignment_type` (`assignment_type`);
