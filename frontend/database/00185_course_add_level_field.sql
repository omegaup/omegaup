ALTER TABLE
    `Courses`
ADD COLUMN
    `level` enum('introductory', 'intermediate', 'advanced') NULL DEFAULT NULL AFTER `acl_id`;