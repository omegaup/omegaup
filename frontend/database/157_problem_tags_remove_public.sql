ALTER TABLE
    `Problems_Tags`
DROP COLUMN
    `public`;

-- We forgot to update these tags (user's feedback)
UPDATE
    `Tags`
SET
    `public` = 1
WHERE
    `name` IN (
        'problemTopic2Sat',
        'problemTopicArrays',
        'problemTopicBacktracking',
        'problemTopicBigNumbers',
        'problemTopicBinarySearch',
        'problemTopicBitmasks',
        'problemTopicBreadthDepthFirstSearch',
        'problemTopicBruteForce',
        'problemTopicBuckets',
        'problemTopicCombinatorics',
        'problemTopicDataStructures',
        'problemTopicDisjointSets',
        'problemTopicDivideAndConquer',
        'problemTopicDynamicProgramming',
        'problemTopicFastFourierTransform',
        'problemTopicGameTheory',
        'problemTopicGeometry',
        'problemTopicGraphTheory',
        'problemTopicGreedy',
        'problemTopicHashing',
        'problemTopicIfElseSwitch',
        'problemTopicImplementation',
        'problemTopicInputOutput',
        'problemTopicLoops',
        'problemTopicMath',
        'problemTopicMatrices',
        'problemTopicMaxFlow',
        'problemTopicMeetInTheMiddle',
        'problemTopicNumberTheory',
        'problemTopicParsing',
        'problemTopicProbability',
        'problemTopicShortestPath',
        'problemTopicSimulation',
        'problemTopicSorting',
        'problemTopicStackQueue',
        'problemTopicStrings',
        'problemTopicSuffixArray',
        'problemTopicSuffixTree',
        'problemTopicTernarySearch',
        'problemTopicTrees',
        'problemTopicTwoPointers'
    );