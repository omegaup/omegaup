CREATE INDEX idx_nomination ON QualityNominations(nomination);
CREATE INDEX idx_nomination_problem ON QualityNominations(nomination, problem_id);
