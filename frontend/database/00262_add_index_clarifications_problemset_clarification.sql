-- Add composite index to optimize getProblemsetClarifications query in DAO/Clarifications.php
-- Supports JOIN on problemset_id and ORDER BY clarification_id DESC, reducing filesort cost

CREATE INDEX idx_clarifications_problemset_clarification
    ON Clarifications (problemset_id, clarification_id DESC);
