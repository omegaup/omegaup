ALTER TABLE
    `Tags`
ADD COLUMN
    `public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el tag es público o no. Los usuarios solo pueden agregar tags privados';

INSERT INTO
    `Tags` (`name`, `public`)
VALUES
    ('problemTagInteractive', 1),
    ('problemTagOutputOnly', 1),
    ('problemTagLanguage', 1),
    ('problemTagKarel', 1),
    ('problemTagInputAndOutput', 1),
    ('problemTagArithmetic', 1),
    ('problemTagConditionals', 1),
    ('problemTagLoops', 1),
    ('problemTagFunctions', 1),
    ('problemTagArrays', 1),
    ('problemTagMatrices', 1),
    ('problemTagCharsAndStrings', 1),
    ('problemTagFormattedInputAndOutput', 1),
    ('problemTagSimulation', 1),
    ('problemTagImplementation', 1),
    ('problemTagAnalyticGeometry', 1),
    ('problemTagSystemsOfEquations', 1),
    ('problemTagDiophantineEquations', 1),
    ('problemTagGCDAndLCM', 1),
    ('problemTagModularArithmetic', 1),
    ('problemTagModularMultiplicativeInverse', 1),
    ('problemTagChineseRemainderTheorem', 1),
    ('problemTagRecursion', 1),
    ('problemTagPermutations', 1),
    ('problemTagCombinations', 1),
    ('problemTagDivisibilityRules', 1),
    ('problemTagCountingProblems', 1),
    ('problemTagCombinatorialDesigns', 1),
    ('problemTagGameTheory', 1),
    ('problemTagNumberTheory', 1),
    ('problemTagNumericalSeries', 1),
    ('problemTagPartialSums', 1),
    ('problemTagPrimalityTest', 1),
    ('problemTagPrimeGeneration', 1),
    ('problemTagPrimeFactorization', 1),
    ('problemTagFourierTransformation', 1),
    ('problemTagBigNumbers', 1),
    ('problemTagBooleanAlgebra', 1),
    ('problemTagBitManipulation', 1),
    ('problemTagProbabilityAndStatistics', 1),
    ('problemTagExponentiationBySquaring', 1),
    ('problemTagSorting', 1),
    ('problemTagBinarySearch', 1),
    ('problemTagExponentialSearch', 1),
    ('problemTagStringMatching', 1),
    ('problemTagStacks', 1),
    ('problemTagQueues', 1),
    ('problemTagLinkedLists', 1),
    ('problemTagHeaps', 1),
    ('problemTagTreeTransversal', 1),
    ('problemTagBinarySearchTree', 1),
    ('problemTagGraphRepresentation', 1),
    ('problemTagGraphConnectivity', 1),
    ('problemTagDirectedGraphs', 1),
    ('problemTagTrees', 1),
    ('problemTagBreadthFirstSearch', 1),
    ('problemTagDepthFirstSearch', 1),
    ('problemTagShortestPaths', 1),
    ('problemTagMinimumSpanningTrees', 1),
    ('problemTagTopologicalSorting', 1),
    ('problemTagGraphsWithNegativeWeights', 1),
    ('problemTagDisjointSets', 1),
    ('problemTagHashing', 1),
    ('problemTagInvertedIndices', 1),
    ('problemTagTries', 1),
    ('problemTagBruteForce', 1),
    ('problemTagIncrementalSearch', 1),
    ('problemTagBacktracking', 1),
    ('problemTagLocalSearch', 1),
    ('problemTagGreedyAlgorithms', 1),
    ('problemTagDivideAndConquer', 1),
    ('problemTagMemorization', 1),
    ('problemTagDynamicProgramming', 1),
    ('problemTagSubArraySearch', 1),
    ('problemTagSubsequenceSearch', 1),
    ('problemTagMeetInTheMiddle', 1),
    ('problemTagBipartiteMatching', 1),
    ('problemTagMaxFlow', 1),
    ('problemTagWaveletTrees', 1),
    ('problemTagSegmentTrees', 1),
    ('problemTagSuffixTrees', 1),
    ('problemTagFenwickTrees', 1),
    ('problemTagLeastCommonAncestor', 1),
    ('problemTagLazyPropagation', 1),
    ('problemTagOfflineQueries', 1),
    ('problemTagSlidingWindow', 1),
    ('problemTagMonotoneStack', 1),
    ('problemTagTwoPointersTechnique', 1),
    ('problemTagSQRTDecomposition', 1),
    ('problemTagPalindromeAlgorithms', 1),
    ('problemTagNearestNeighbors', 1),
    ('problemTagConvexHull', 1),
    ('problemTagSweepLine', 1),
    ('problemTagLexingAndParsing', 1),
    ('problemTagGeneticAlgorithms', 1),
    ('problemTagParticleSwarmOptimization', 1),
    ('problemTagHeuristics', 1),
    ('problemTagDataCompression', 1),
    ('problemTagBigData', 1),
    ('problemTagOMI', 1),
    ('problemTagOMIAguascalientes', 1),
    ('problemTagOMIBajaCalifornia', 1),
    ('problemTagOMIBajaCaliforniaSur', 1),
    ('problemTagOMICampeche', 1),
    ('problemTagOMICoahuila', 1),
    ('problemTagOMIColima', 1),
    ('problemTagOMIChiapas', 1),
    ('problemTagOMIChihuahua', 1),
    ('problemTagOMIDistritoFederal', 1),
    ('problemTagOMIDurango', 1),
    ('problemTagOMIGuanajuato', 1),
    ('problemTagOMIGuerrero', 1),
    ('problemTagOMIHidalgo', 1),
    ('problemTagOMIJalisco', 1),
    ('problemTagOMIMexico', 1),
    ('problemTagOMIMichoacan', 1),
    ('problemTagOMIMorelos', 1),
    ('problemTagOMINayarit', 1),
    ('problemTagOMINuevoLeon', 1),
    ('problemTagOMIOaxaca', 1),
    ('problemTagOMIPuebla', 1),
    ('problemTagOMIQueretaro', 1),
    ('problemTagOMIQuintanaRoo', 1),
    ('problemTagOMISanLuisPotosi', 1),
    ('problemTagOMISinaloa', 1),
    ('problemTagOMISonora', 1),
    ('problemTagOMITabasco', 1),
    ('problemTagOMITamaulipas', 1),
    ('problemTagOMITlaxcala', 1),
    ('problemTagOMIVeracruz', 1),
    ('problemTagOMIYucatan', 1),
    ('problemTagOMIZacatecas', 1),
    ('problemTagOMIPS', 1),
    ('problemTagIOI', 1),
    ('problemTagICPC', 1),
    ('problemTagCIIC', 1),
    ('problemTagCodingCup', 1),
    ('problemTagCodingRush', 1),
    ('problemTagCOCI', 1),
    ('problemTagBOI', 1);

UPDATE
    `Tags`
SET
    `public` = 1
WHERE
    `name` IN (
        'problemLevelIntermediateAnalysisAndDesignOfAlgorithms',
        'problemLevelAdvancedCompetitiveProgramming',
        'problemLevelIntermediateDataStructuresAndAlgorithms',
        'problemLevelBasicIntroductionToProgramming',
        'problemLevelBasicKarel',
        'problemLevelIntermediateMathsInProgramming',
        'problemLevelAdvancedSpecializedTopics'
    );