CREATE INDEX idx_submissions_identity_type_problemset
ON Submissions(identity_id, type, problemset_id);
