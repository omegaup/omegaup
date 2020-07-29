-- Create new problem-tag relations and delete old ones

SELECT @oldTag := tag_id FROM Tags WHERE name = 'segment-tree';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSegmentTrees';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'grafo-bipartito';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBipartiteMatching';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'prefix-sum';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagPartialSums';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'algoritmo-genetico';
SELECT @newTag := tag_id FROM Tags WHERE name = 'prÏoblemTagGeneticAlgorithms';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'genetic-algorithm';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGeneticAlgorithms';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'gloton';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGreedyAlgorithms';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'greedy';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGreedyAlgorithms';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'arboles';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTrees';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'tree';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTrees';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'mst';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMinimumSpanningTrees';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'map';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBinarySearchTree';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'set';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBinarySearchTree';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'fenwick-tree';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagFenwickTrees';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'wavelet-tree';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagWaveletTrees';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'residuos';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagModularArithmetic';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'aritmetica-modular';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagModularArithmetic';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'modulo';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagModularArithmetic';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'manejo-indices';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagArrays';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'arreglos';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagArrays';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'vectores';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagArrays';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'boi';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBOI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'binary-search';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBinarySearch';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'busqueda-binaria';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBinarySearch';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'pruning';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBacktracking';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'backtracking';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBacktracking';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'bfs';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBreadthFirstSearch';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'busqueda-en-amplitud';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBreadthFirstSearch';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'dfs';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDepthFirstSearch';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'busqueda-incremental';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagIncrementalSearch';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'dijkstra';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagShortestPaths';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'cadenas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCharsAndStrings';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'manipulacion-cadenas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCharsAndStrings';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'strings';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCharsAndStrings';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'strings-dp';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCharsAndStrings';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDynamicProgramming';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'convex-hull';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagConvexHull';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ciclos';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLoops';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ciclos-anidados';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLoops';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ciclos-con-if';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLoops';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'iteracion';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLoops';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'iterate';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLoops';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'while';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLoops';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ciic';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCIIC';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ciic2018';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCIIC';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'coci';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCOCI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'coding-cup';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCodingCup';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'coding-rush';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCodingRush';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'colas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagQueues';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'floodfill';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGraphConnectivity';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'strongly-connected-components';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGraphConnectivity';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'union-find';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDisjointSets';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'dsu';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDisjointSets';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'hashin';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagHashing';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'hash';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagHashing';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'hashing';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagHashing';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'divide-and-conquer';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDivideAndConquer';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ecuacion-diofantina';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDiophantineEquations';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'easy-inout';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagInputAndOutput';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'entrada-salida';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagInputAndOutput';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'imprimir';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagInputAndOutput';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'lazy-propagation';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLazyPropagation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'potencias';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagExponentiationBySquaring';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'exponenciacion-rapida';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagExponentiationBySquaring';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'flujos';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMaxFlow';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'beepers';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'busqueda-exhaustiva';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBruteForce';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'brute';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBruteForce';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'brute-force';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBruteForce';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'funciones';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagFunctions';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'criba-de-eratostenes';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagPrimeGeneration';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'geometrico';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagAnalyticGeometry';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'heuristica';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagHeuristics';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'icpc';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagICPC';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'icpc2014-latam';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagICPC';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'the-2014-southeast-usa-regional-contest';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagICPC';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'the-2016-acm-itesg-local-programming-contest';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagICPC';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ad-hoc';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagImplementation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'adhoc';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagImplementation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'implementacion';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagImplementation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'implementation';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagImplementation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'libinteractive';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagInteractive';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ioi';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagIOI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ioi-1617-ps2';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagIOI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'mano-derecha';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'karel-dinamica';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'karel-recusirvidad-kinder';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagRecursion';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'karelotitlan-recursion';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagRecursion';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'recursion-con-parametros';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagRecursion';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'recursividad-deshabilitada';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagRecursion';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'recursividad-kinder';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagKarel';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'listas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLinkedLists';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'bitmask';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBitManipulation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'bits';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBitManipulation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'bits-';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBitManipulation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'recorrido-matrices';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMatrices';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'matrices';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMatrices';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'matrices-cadenas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMatrices';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCharsAndStrings';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'memorizacion';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMemorization';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'meet-in-the-middle';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMeetInTheMiddle';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'fracciones';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGCDAndLCM';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'gcd';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGCDAndLCM';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'lca';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGCDAndLCM';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'bignum';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBigNumbers';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'numeros-grandes';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBigNumbers';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi2010';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi2015';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi-2016';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi2016';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi-2017';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi2017';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi2018';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omi-2019';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'estatal-aguascalientes';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIAguascalientes';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omibc';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIBajaCalifornia';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omibc-2016';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIBajaCalifornia';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'campeche';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMICampeche';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'chihuahua';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIChihuahua';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'oieg';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIGuanajuato';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'orig';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIGuanajuato';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omiijal';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIJalisco';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omijal';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIJalisco';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'tabasco';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMITabasco';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'olimpiada-veracruzana-de-informatica-2019';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIVeracruz';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'olimpiada-veracruzana-de-informatica-2019-ovi-2019';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIVeracruz';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ovi';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIVeracruz';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'preselectivo-nacional-ovi';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIVeracruz';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'preselectivonacional-ovi';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIVeracruz';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'problema-de-olimpiada-veracruzana-de-informatica-2';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIVeracruz';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMI';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omip';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omips';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omip2017';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omips2017';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omis2017';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omips2018';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omips2019';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'omips2020';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagOMIPS';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'counting-sort';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSorting';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'burbuja';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSorting';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ordenamiento';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSorting';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'sorting';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSorting';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'topological-sort';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTopologicalSorting';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'next-permutation';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagPermutations';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'suma-acumulada';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagPartialSums';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'pilas-y-colas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagStacks';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagQueues';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'pilas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagStacks';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'combinatoria';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCountingProblems';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'conteo';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCountingProblems';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'counting';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagCountingProblems';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'knapsack';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDynamicProgramming';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'lis';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDynamicProgramming';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSubsequenceSearch';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'dp';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDynamicProgramming';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'programacion-dinamica';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDynamicProgramming';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'recursion';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagRecursion';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'divisibilidad';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDivisibilityRules';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'grafos';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGraphRepresentation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'graph-theory';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGraphRepresentation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'series-numericas';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagNumericalSeries';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'simulacion';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSimulation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'simulacion7';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSimulation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ecuacion-cuadratica';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSystemsOfEquations';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'two-pointer';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTwoPointersTechnique';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'two-pointer-tle';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTwoPointersTechnique';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'game-theory';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGameTheory';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'min-max';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagGameTheory';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'teoria-de-numeros';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagNumberTheory';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'number-theory';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagNumberTheory';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'if-anidado';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagConditionals';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'comparacion';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagConditionals';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'condicionales';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagConditionals';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'if';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagConditionals';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'if-else';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagConditionals';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'if-then-else';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagConditionals';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'fft';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagFourierTransformation';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'trie';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTries';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'trie-dp';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTries';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagDynamicProgramming';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'sliding-window';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSlidingWindow';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'bellman';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagShortestPaths';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'bst';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagBinarySearchTree';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'dos-punteros';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagTwoPointersTechnique';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'lista-enlazada';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagLinkedLists';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'matriz';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagMatrices';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'pila';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagStacks';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'suma-de-acumulados';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagPartialSums';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'tabla-hash';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagHashing';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;

SELECT @oldTag := tag_id FROM Tags WHERE name = 'ventana-deslizante';
SELECT @newTag := tag_id FROM Tags WHERE name = 'problemTagSlidingWindow';
INSERT INTO `Problems_Tags` (`problem_id`, `tag_id`, `public`, `source`)
SELECT problem_id, @newTag, '1', 'owner' 
	FROM `Problems_Tags` 
	WHERE `Problems_Tags`.`tag_id` = @oldTag;
DELETE FROM Problems_Tags WHERE tag_id = @oldTag;