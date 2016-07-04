
ALTER TABLE Users ADD COLUMN interviewer BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE Contests ADD COLUMN interview BOOL NOT NULL DEFAULT  '0' COMMENT  'Si este concurso es en realidad una entrevista';

