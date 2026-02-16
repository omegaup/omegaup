-- Add index to Contests table on title, archived columns
CREATE INDEX idx_contests_title_archived ON Contests (title, archived);
