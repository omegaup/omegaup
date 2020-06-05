DELETE FROM
    `Problem_Tags`
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
    `name` = 'problemLevelIntermediateAnalysisAndDesignOfAlgorithms'
WHERE
    `name` = 'problemCategoryAlgorithmAndNetworkOptimization';

UPDATE
    `Tags`
SET
    `name` = 'problemLevelAdvancedCompetitiveProgramming'
WHERE
    `name` = 'problemCategoryCompetitiveProgramming';

UPDATE
    `Tags`
SET
    `name` = 'problemLevelIntermediateDataStructuresAndAlgorithms'
WHERE
    `name` = 'problemCategoryElementaryDataStructures';

UPDATE
    `Tags`
SET
    `name` = 'problemLevelBasicIntroductionToProgramming'
WHERE
    `name` = 'problemCategoryIntroductionToProgramming';

UPDATE
    `Tags`
SET
    `name` = 'problemLevelBasicKarel'
WHERE
    `name` = 'problemCategoryKarelEducation';

UPDATE
    `Tags`
SET
    `name` = 'problemLevelIntermediateMathsInProgramming'
WHERE
    `name` = 'problemCategoryMathematicalProblems';

UPDATE
    `Tags`
SET
    `name` = 'problemLevelAdvancedSpecializedTopics'
WHERE
    `name` = 'problemCategorySpecializedTopics';
