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
    `name` = 'problemTagInteractive'
WHERE
    `name` IN (
        'libinteractive',
        'interactive'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagSegmentTrees'
WHERE
    `name` = 'segment-tree';

UPDATE
    `Tags`
SET
    `name` = 'problemTagBipartiteMatching'
WHERE
    `name` = 'grafo-bipartito';

UPDATE
    `Tags`
SET
    `name` = 'problemTagBipartiteMatching'
WHERE
    `name` = 'grafo-bipartito';

UPDATE
    `Tags`
SET
    `name` = 'problemTagPartialSums'
WHERE
    `name` IN (
        'prefix-sum',
        'prefixsum'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagGeneticAlgorithms'
WHERE
    `name` IN (
        'algoritmo-genetico',
        'genetic-algorithm'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagGreedyAlgorithms'
WHERE
    `name` IN (
        'gloton',
        'greedy'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagTrees'
WHERE
    `name` IN (
        'arboles',
        'tree'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagMinimumSpanningTrees'
WHERE
    `name` = 'mst';

UPDATE
    `Tags`
SET
    `name` = 'problemTagBinarySearchTree'
WHERE
    `name` IN (
        'map',
        'set'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagFenwickTrees'
WHERE
    `name` = 'fenwick-tree';

UPDATE
    `Tags`
SET
    `name` = 'problemTagWaveletTrees'
WHERE
    `name` = 'wavelet-tree';

UPDATE
    `Tags`
SET
    `name` = 'problemTagModularArithmetic'
WHERE
    `name` IN (
        'residuos',
        'aritmetica-modular',
        'modulo'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagArrays'
WHERE
    `name` IN (
        'manejo-indices',
        'arreglos',
        'vectores'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagBOI'
WHERE
    `name` = 'boi';

UPDATE
    `Tags`
SET
    `name` = 'problemTagBinarySearch'
WHERE
    `name` IN (
        'binary-search',
        'busqueda-binaria'
    );

UPDATE
    `Tags`
SET
    `name` = 'problemTagBacktracking'
WHERE
    `name` IN (
        'pruning',
        'backtracking'
    );

UPDATE
    `Tags`
SET
    `name` = ''
WHERE
    `name` IN (
        '',
        ''
    );




