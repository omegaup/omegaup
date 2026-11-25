-- Optimize QualityNominations list query: support filter by nomination + ORDER BY qualitynomination_id
-- Eliminates "Using temporary; Using filesort"
CREATE INDEX idx_nomination_qualitynomination ON QualityNominations(nomination, qualitynomination_id);

-- Optimize getVotesForNomination: support correlated subquery for last vote per qualitynomination_id, user_id
-- Eliminates full scan on QualityNomination_Comments for MAX(qualitynomination_comment_id) lookup
CREATE INDEX idx_qnc_nomination_user_comment ON QualityNomination_Comments(
    qualitynomination_id,
    user_id,
    qualitynomination_comment_id
);
