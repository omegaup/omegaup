-- Problems
ALTER TABLE `Problems`
	MODIFY COLUMN `visibility` int(1) NOT NULL DEFAULT '1' COMMENT '-1 banned, 0 private, 1 public, 2 recommended';

-- QualityNomination_Comments
ALTER TABLE `QualityNomination_Comments`
	MODIFY COLUMN `vote` int(1) NOT NULL COMMENT 'El voto emitido en este comentario. En el rango de [-2, +2]';
