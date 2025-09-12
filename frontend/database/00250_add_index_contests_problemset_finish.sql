-- Add index to Contests table on problemset_id, finish_time column
CREATE INDEX idx_contests_problemset_finish ON Contests (finish_time, problemset_id);
