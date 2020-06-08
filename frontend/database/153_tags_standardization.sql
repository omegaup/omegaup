-- First, make sure that private tags, are actually private
UPDATE
    `Problems_Tags` AS `pt`
SET
    `pt`.`source` = 'owner'
WHERE
    `pt`.`tag_id` IN (
        SELECT
            `t`.`tag_id`
        FROM
            `Tags` AS `t`
        WHERE
            `t`.`name` IN (
                'barrido',
                'busqueda',
                'condiciones-in-out',
                'optimizacion',
                'primos',
                'recorridos',
                'sin-casos',
                'stl',
                'stl-basico',
                'tutorial-karel',
                'caminos',
                'data-structures',
                'intro',
                'introduccion',
                'matematicas',
                'math',
                'pes-2018',
                'pes-2019',
                'pes-2020',
                'usaco',
                '-comparaciones',
                '-ip',
                '-palabras',
                '2010',
                '2012',
                '2013',
                '2014-2015',
                '2014-southeastusa-regional-contest',
                '2016-2017',
                '2017',
                '2017-2018',
                '2018',
                '2019',
                '2020',
                'a',
                'acumulacion',
                'ag',
                'apoquinto',
                'basic-geometry-scanf-printf',
                'basico',
                'basico4',
                'beepers',
                'calculo-diferencial',
                'casos-muy-random',
                'chido',
                'codeforces',
                'construcciones',
                'contando-monedas',
                'cp',
                'cpom2015',
                'cpp',
                'curso-omi',
                'dados',
                'dia-de-sitemas-itesg-2015',
                'dividiendo-monedas',
                'easy',
                'ejercicio',
                'elemental',
                'estatal',
                'estatal-2014',
                'explora',
                'fisica',
                'g',
                'gcj',
                'gto',
                'gto-tutorial-karel',
                'hola',
                'int',
                'karel-dinamica',
                'karelotitlan',
                'la-5ta-tortuga-es-el-techer-vela-o',
                'lbm',
                'leng',
                'lenguaje',
                'lineal',
                'mat',
                'matematicas-estructuras-de-datos',
                'medio',
                'memoria-limitada',
                'monedas',
                'monedas-contando-monedas',
                'multivalor',
                'name',
                'nivel-',
                'nivel-0',
                'nivel-1',
                'nivel-2',
                'nivel-3',
                'no-recursivo',
                'number',
                'oeig',
                'oie',
                'omegaup',
                'operacion',
                'patrones',
                'pb',
                'preestatal',
                'include-poetry',
                'preselectivo',
                'project-euler',
                'quimica',
                'semana-de-sistemas-itesg-2015',
                'tacos-al-pastor',
                'teddy',
                'teoria',
                'tojupro19',
                'training-gate',
                'tutorial',
                'uam',
                'validador',
                'variables'
            )
    );

-- Now, migrate the current tags to the new ones, one by one
UPDATE
    `Tags`
SET
    `name` = CASE
        WHEN `name` = 'segment-tree'
            THEN 'problemTagSegmentTrees'
        WHEN `name` = 'grafo-bipartito'
            THEN 'problemTagBipartiteMatching'
        WHEN `name` IN (
            'prefix-sum',
            'prefixsum'
        )
            THEN 'problemTagPartialSums'
        WHEN `name` IN (
            'algoritmo-genetico',
            'genetic-algorithm'
        )
            THEN 'problemTagGeneticAlgorithms'
        WHEN `name` IN (
            'gloton',
            'greedy'
        )
            THEN 'problemTagGreedyAlgorithms'
        WHEN `name` IN (
            'arboles',
            'tree'
        )
            THEN 'problemTagTrees'
        WHEN `name` = 'mst'
            THEN 'problemTagMinimumSpanningTrees'
        WHEN `name` IN (
            'map',
            'set'
        )
            THEN 'problemTagBinarySearchTree'
        WHEN `name` = 'fenwick-tree'
            THEN 'problemTagFenwickTrees'
        WHEN `name` = 'wavelet-tree'
            THEN 'problemTagWaveletTrees'
        WHEN `name` IN (
            'residuos',
            'aritmetica-modular',
            'modulo'
        )
            THEN 'problemTagModularArithmetic'
        WHEN `name` IN (
            'manejo-indices',
            'arreglos',
            'vectores'
        )
            THEN 'problemTagArrays'
        WHEN `name` = 'boi'
            THEN 'problemTagBOI'
        WHEN `name` IN (
            'binary-search',
            'busqueda-binaria'
        )
            THEN 'problemTagBinarySearch'
        WHEN `name` IN (
            'pruning',
            'backtracking'
        )
            THEN 'problemTagBacktracking'
        WHEN `name` IN (
            'bfs',
            'busqueda-en-amplitud'
        )
            THEN 'problemTagBreadthFirstSearch'
        WHEN `name` = 'dfs'
            THEN 'problemTagDepthFirstSearch'
        WHEN `name` = 'busqueda-incremental'
            THEN 'problemTagIncrementalSearch'
        WHEN `name` = 'dijkstra'
            THEN 'problemTagShortestPaths'
        WHEN `name` IN (
            'cadenas',
            'manipulacion-cadenas',
            'strings',
            'strings-dp'
        )
            THEN 'problemTagCharsAndStrings'
        WHEN `name` = 'convex-hull'
            THEN 'problemTagConvexHull'
        WHEN `name` IN (
            'ciclos',
            'ciclos-anidados',
            'ciclos-con-if',
            'iteracion',
            'iterate',
            'while'
        )
            THEN 'problemTagLoops'
        WHEN `name` IN (
            'ciic',
            'ciic2018'
        )
            THEN 'problemTagCIIC'
        WHEN `name` = 'coci'
            THEN 'problemTagCOCI'
        WHEN `name` = 'coding-cup'
            THEN 'problemTagCodingCup'
        WHEN `name` = 'coding-rush'
            THEN 'problemTagCodingRush'
        WHEN `name` = 'colas'
            THEN 'problemTagQueues'
        WHEN `name` IN (
            'floodfill',
            'strongly-connected-components'
        )
            THEN 'problemTagGraphConnectivity'
        WHEN `name` IN (
            'union-find',
            'dsu'
        )
            THEN 'problemTagDisjointSets'
        WHEN `name` IN (
            'hashin',
            'hash',
            'hashing'
        )
            THEN 'problemTagHashing'
        WHEN `name` = 'divide-and-conquer'
            THEN 'problemTagDivideAndConquer'
        WHEN `name` = 'ecuacion-diofantina'
            THEN 'problemTagDiophantineEquations'
        WHEN `name` IN (
            'easy-inout',
            'entrada-salida',
            'imprimir'
        )
            THEN 'problemTagInputAndOutput'
        WHEN `name` = 'lazy-propagation'
            THEN 'problemTagLazyPropagation'
        WHEN `name` IN (
            'potencias',
            'exponenciacion-rapida'
        )
            THEN 'problemTagExponentiationBySquaring'
        WHEN `name` = 'flujos'
            THEN 'problemTagMaxFlow'
        WHEN `name` IN (
            'busqueda-exhaustiva',
            'brute',
            'brute-force'
        )
            THEN 'problemTagBruteForce'
        WHEN `name` = 'funciones'
            THEN 'problemTagFunctions'
        WHEN `name` = 'criba-de-eratostenes'
            THEN 'problemTagPrimeGeneration'
        WHEN `name` = 'geometrico'
            THEN 'problemTagAnalyticGeometry'
        WHEN `name` = 'heuristica'
            THEN 'problemTagHeuristics'
        WHEN `name` IN (
            'icpc'
            'icpc2014-latam'
            'the-2014-southeast-usa-regional-contest'
            'the-2016-acm-itesg-local-programming-contest'
        )
            THEN 'problemTagICPC'
        WHEN `name` IN (
            'ad-hoc',
            'adhoc',
            'implementacion',
            'implementation'
        )
            THEN 'problemTagImplementation'
        WHEN `name` IN (
            'ioi',
            'ioi-1617-ps2'
        )
            THEN 'problemTagIOI'
        WHEN `name` = 'listas'
            THEN 'problemTagLinkedLists'
        WHEN `name` IN (
            'bitmask',
            'bits',
            'bits-'
        )
            THEN 'problemTagBitManipulation'
        WHEN `name` IN (
            'recorrido-matrices',
            'matrices',
            'matrices-cadenas'
        )
            THEN 'problemTagMatrices'
        WHEN `name` = 'memorizacion'
            THEN 'problemTagMemorization'
        WHEN `name` = 'meet-in-the-middle'
            THEN 'problemTagMeetInTheMiddle'
        WHEN `name` IN (
            'fracciones',
            'gcd',
            'lca'
        )
            THEN 'problemTagGCDAndLCM'
        WHEN `name` IN (
            'bignum',
            'numeros-grandes'
        )
            THEN 'problemTagBigNumbers'
        WHEN `name` IN (
            'omi',
            'omi2010',
            'omi2015',
            'omi-2016',
            'omi2016',
            'omi-2017',
            'omi2017',
            'omi2018',
            'omi-2019'
        )
            THEN 'problemTagOMI'
        WHEN `name` = 'estatal-aguascalientes'
            THEN 'problemTagOMIAguascalientes'
        WHEN `name` IN (
            'omibc',
            'omibc-2016'
        )
            THEN 'problemTagOMIBajaCalifornia'
        WHEN `name` = 'campeche'
            THEN 'problemTagOMICampeche'
        WHEN `name` = 'chihuahua'
            THEN 'problemTagOMIChihuahua'
        WHEN `name` IN (
            'oieg',
            'orig'
        )
            THEN 'problemTagOMIGuanajuato'
        WHEN `name` IN (
            'omiijal',
            'omijal'
        )
            THEN 'problemTagOMIJalisco'
        WHEN `name` = 'tabasco'
            THEN 'problemTagOMITabasco'
        WHEN `name` IN (
            'olimpiada-veracruzana-de-informatica-2019',
            'olimpiada-veracruzana-de-informatica-2019-ovi-2019',
            'ovi',
            'preselectivo-nacional-ovi',
            'preselectivonacional-ovi',
            'problema-de-olimpiada-veracruzana-de-informatica-2'
        )
            THEN 'problemTagOMIVeracruz'
        WHEN `name` IN (
            'omip',
            'omips',
            'omip2017',
            'omips2017',
            'omis2017',
            'omips2018',
            'omips2019',
            'omips2020'
        )
            THEN 'problemTagOMIPS'
        WHEN `name` IN (
            'counting-sort',
            'burbuja',
            'ordenamiento',
            'sorting'
        )
            THEN 'problemTagSorting'
        WHEN `name`  = 'topological-sort'
            THEN 'problemTagTopologicalSorting'
        WHEN `name` = 'next-permutation'
            THEN 'problemTagPermutations'
        WHEN `name` = 'suma-acumulada'
            THEN 'problemTagOfflineQueries'
        WHEN `name` IN (
            'pilas-y-colas',
            'pilas'
        )
            THEN 'problemTagStacks'
        WHEN `name` IN (
            'combinatoria',
            'conteo',
            'counting'
        )
            THEN 'problemTagCountingProblems'
        WHEN `name` IN (
            'knapsack',
            'lis',
            'dp',
            'programacion-dinamica'
        )
            THEN 'problemTagDynamicProgramming'
        WHEN `name` = 'recursion'
            THEN 'problemTagRecursion'
        WHEN `name` = 'divisibilidad'
            THEN 'problemTagDivisibilityRules'
        WHEN `name` IN (
            'grafos',
            'graph-theory'
        )
            THEN 'problemTagGraphRepresentation'
        WHEN `name` = 'series-numericas'
            THEN 'problemTagNumericalSeries'
        WHEN `name` IN (
            'simulacion',
            'simulacion7'
        )
            THEN 'problemTagSimulation'
        WHEN `name` = 'ecuacion-cuadratica'
            THEN 'problemTagSystemsOfEquations'
        WHEN `name` IN (
            'two-pointer',
            'two-pointer-tle'
        )
            THEN 'problemTagTwoPointersTechnique'
        WHEN `name` IN (
            'game-theory',
            'min-max'
        )
            THEN 'problemTagGameTheory'
        WHEN `name` IN (
            'teoria-de-numeros',
            'number-theory'
        )
            THEN 'problemTagNumberTheory'
        WHEN `name` IN (
            'if-anidado',
            'comparacion',
            'condicionales',
            'if',
            'if-else',
            'if-then-else'
        )
            THEN 'problemTagConditionals'
        WHEN `name` = 'fft'
            THEN 'problemTagFourierTransformation'
        WHEN `name` IN (
            'trie',
            'trie-dp'
        )
            THEN 'problemTagTries'
        WHEN `name` = 'sliding-window'
            THEN 'problemTagSlidingWindow'
        -- WHEN `name` = 'solo-salida'
        --     THEN 'problemTagOutputOnly'
        -- WHEN `name` = 'lenguaje'
        --     THEN 'problemTagLanguage'
        WHEN `name` IN (
            'libinteractive'
            -- 'interactive'
        )
            THEN 'problemTagInteractive'
        WHEN `name` IN (
            'mano-derecha',
            -- 'karel',
            'karel-recusirvidad-kinder',
            'karelotitlan-recursion',
            'recursion-con-parametros',
            'recursividad-deshabilitada',
            'recursividad-kinder'
        )
            THEN 'problemTagKarel'
        ELSE `name`
        END;

-- Finally, insert the new tags
INSERT INTO
    `Tags` (`name`)
VALUES
    ('problemTagArithmetic'),
    ('problemTagFormattedInputAndOutput'),
    ('problemTagModularMultiplicativeInverse'),
    ('problemTagChineseRemainderTheorem'),
    ('problemTagCombinations'),
    ('problemTagCombinatorialDesigns'),
    ('problemTagPrimalityTest'),
    ('problemTagPrimeFactorization'),
    ('problemTagBooleanAlgebra'),
    ('problemTagProbabilityAndStatistics'),
    ('problemTagExponentialSearch'),
    ('problemTagStringMatching'),
    ('problemTagHeaps'),
    ('problemTagTreeTransversal'),
    ('problemTagDirectedGraphs'),
    ('problemTagGraphsWithNegativeWeights'),
    ('problemTagInvertedIndices'),
    ('problemTagLocalSearch'),
    ('problemTagSubArraySearch'),
    ('problemTagSubsequenceSearch'),
    ('problemTagSuffixTrees'),
    ('problemTagLeastCommonAncestor'),
    ('problemTagMonotoneStack'),
    ('problemTagSQRTDecomposition'),
    ('problemTagPalindromeAlgorithms'),
    ('problemTagNearestNeighbors'),
    ('problemTagSweepLine'),
    ('problemTagLexingAndParsing'),
    ('problemTagParticleSwarmOptimization'),
    ('problemTagDataCompression'),
    ('problemTagBigData'),
    ('problemTagOMIBajaCaliforniaSur'),
    ('problemTagOMICoahuila'),
    ('problemTagOMIColima'),
    ('problemTagOMIChiapas'),
    ('problemTagOMIDistritoFederal'),
    ('problemTagOMIDurango'),
    ('problemTagOMIGuerrero'),
    ('problemTagOMIHidalgo'),
    ('problemTagOMIMexico'),
    ('problemTagOMIMichoacan'),
    ('problemTagOMIMorelos'),
    ('problemTagOMINayarit'),
    ('problemTagOMINuevoLeon'),
    ('problemTagOMIOaxaca'),
    ('problemTagOMIPuebla'),
    ('problemTagOMIQueretaro'),
    ('problemTagOMIQuintanaRoo'),
    ('problemTagOMISanLuisPotosi'),
    ('problemTagOMISinaloa'),
    ('problemTagOMISonora'),
    ('problemTagOMITamaulipas'),
    ('problemTagOMITlaxcala'),
    ('problemTagOMIYucatan'),
    ('problemTagOMIZacatecas');
