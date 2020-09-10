ALTER TABLE
    `Tags`
ADD COLUMN
    `icon` varchar(50) DEFAULT NULL COMMENT 'Indica el icono del tag según el tipo de problemLevel que represente';

UPDATE
    `Tags`
SET
    `icon` = CASE  `name`
		WHEN 'problemLevelBasicKarel'
			THEN 'fas fa-robot'
		WHEN 'problemLevelBasicIntroductionToProgramming'
			THEN 'fas fa-laptop-code'
		WHEN 'problemLevelIntermediateMathsInProgramming'
			THEN 'fas fa-square-root-alt'
		WHEN 'problemLevelIntermediateDataStructuresAndAlgorithms'
			THEN 'fas fa-project-diagram'
		WHEN 'problemLevelIntermediateAnalysisAndDesignOfAlgorithms'
			THEN 'fas fa-sitemap'
		WHEN 'problemLevelAdvancedCompetitiveProgramming'
			THEN 'fas fa-trophy'
		WHEN 'problemLevelAdvancedSpecializedTopics'
			THEN 'fas fa-code'
		ELSE
			`icon`
		END;