DELETE FROM
    `Problems_Tags`
WHERE
    `source` = 'quality';

ALTER TABLE
    `Tags`
MODIFY COLUMN
    `name` varchar(75) NOT NULL;

DELETE FROM
    `Tags`
WHERE
    `name` = 'problemCategoryOpenResponse';

UPDATE
    `Tags`
SET
    `name` = CASE `name`
        WHEN 'problemCategoryAlgorithmAndNetworkOptimization'
            THEN 'problemLevelIntermediateAnalysisAndDesignOfAlgorithms'
        WHEN 'problemCategoryCompetitiveProgramming'
            THEN 'problemLevelAdvancedCompetitiveProgramming'
        WHEN 'problemCategoryElementaryDataStructures'
            THEN 'problemLevelIntermediateDataStructuresAndAlgorithms'
        WHEN 'problemCategoryIntroductionToProgramming'
            THEN 'problemLevelBasicIntroductionToProgramming'
        WHEN 'problemCategoryKarelEducation'
            THEN 'problemLevelBasicKarel'
        WHEN 'problemCategoryMathematicalProblems'
            THEN 'problemLevelIntermediateMathsInProgramming'
        WHEN 'problemCategorySpecializedTopics'
            THEN 'problemLevelAdvancedSpecializedTopics'
        ELSE
            `name`
        END;
