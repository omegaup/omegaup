<?php

namespace OmegaUp\Psalm;

class RequestParamChecker implements
    \Psalm\Plugin\Hook\AfterExpressionAnalysisInterface,
    \Psalm\Plugin\Hook\AfterMethodCallAnalysisInterface,
    \Psalm\Plugin\Hook\AfterClassLikeAnalysisInterface {
    /**
     * @var array<string, array<string, RequestParamDescription>>
     */
    private static $methodTypeMapping = [];

    /**
     * @var array<string, array<string, RequestParamDescription>>
     */
    private static $parsedMethodTypeMapping = [];

    /**
     * @var array<string, array<string, true>>
     */
    private static $methodCallGraph = [];

    /**
     * Called for every Request property fetch.
     *
     * @param \Psalm\FileManipulation[] $fileReplacements
     *
     * @return null|false
     */
    private static function processRequestPropertyFetch(
        \PhpParser\Node\Expr\ArrayDimFetch $expr,
        \Psalm\Context $context,
        \Psalm\StatementsSource $statementsSource,
        \Psalm\Codebase $codebase,
        array &$fileReplacements = []
    ) {
        $varType = $statementsSource->getNodeTypeProvider()->getType(
            $expr->var
        );
        if (is_null($varType)) {
            return null;
        }
        $foundRequest = false;
        foreach ($varType->getAtomicTypes() as $typeName => $type) {
            if (
                $type instanceof \Psalm\Type\Atomic\TNamedObject &&
                $type->value == 'OmegaUp\\Request'
            ) {
                $foundRequest = true;
                break;
            }
        }
        if (!$foundRequest) {
            return null;
        }
        if (!$expr->dim instanceof \PhpParser\Node\Scalar\String_) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new RequestAccessNotALiteralString(
                        'Request array access not a literal string',
                        new \Psalm\CodeLocation($statementsSource, $expr)
                    ),
                    $statementsSource->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }
        if (is_null($context->calling_function_id)) {
            throw new \Exception('Should never happen');
        }
        $functionId = strtolower($context->calling_function_id);
        if (!array_key_exists($functionId, self::$methodTypeMapping)) {
            self::$methodTypeMapping[$functionId] = [];
        }
        self::$methodTypeMapping[$functionId][$expr->dim->value] = new RequestParamDescription(
            \Psalm\Type::getMixed(),
            $expr->dim->value
        );
        return null;
    }

    /**
     * Called after a statement has been checked
     *
     * @param \Psalm\FileManipulation[] $fileReplacements
     *
     * @return null|false
     */
    public static function afterExpressionAnalysis(
        \PhpParser\Node\Expr $expr,
        \Psalm\Context $context,
        \Psalm\StatementsSource $statementsSource,
        \Psalm\Codebase $codebase,
        array &$fileReplacements = []
    ) {
        if (
            $context->parent !== 'OmegaUp\\Controllers\\Controller' &&
            $context->self !== 'OmegaUp\\Controllers\\Controller'
        ) {
            return null;
        }
        if ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return self::processRequestPropertyFetch(
                $expr,
                $context,
                $statementsSource,
                $codebase,
                $fileReplacements
            );
        }

        return null;
    }

    private static function processClass(
        \PhpParser\Node\Stmt\ClassLike $classStmt,
        string $className
    ): void {
        foreach ($classStmt->stmts as $methodStmt) {
            if (!$methodStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $functionId = strtolower(
                "{$className}::{$methodStmt->name->name}"
            );
            if (array_key_exists($functionId, self::$parsedMethodTypeMapping)) {
                continue;
            }
            self::$parsedMethodTypeMapping[$functionId] = [];

            $docblock = $methodStmt->getDocComment();
            if (is_null($docblock)) {
                continue;
            }

            $parsedDocComment = \Psalm\DocComment::parse($docblock->getText());
            if (isset($parsedDocComment['specials']['omegaup-request-param'])) {
                foreach (
                    $parsedDocComment['specials']['omegaup-request-param'] as $requestParam
                ) {
                    if (
                        preg_match(
                            '/^([^$]+)\s+\$([_a-zA-Z]\S*)\s*(\S.*)?$/',
                            $requestParam,
                            $matches,
                            PREG_UNMATCHED_AS_NULL
                        ) !== 1
                    ) {
                        continue;
                    }
                    if (count($matches) == 4) {
                        /** @var array{0: string, 1: string, 2: string, 3: ?string} $matches */
                        [
                            $_,
                            $annotationType,
                            $annotationVariable,
                            $annotationDescription,
                        ] = $matches;
                    } else {
                        /** @var array{0: string, 1: string, 2: string} $matches */
                        [
                            $_,
                            $annotationType,
                            $annotationVariable,
                        ] = $matches;
                        $annotationDescription = null;
                    }
                    self::$parsedMethodTypeMapping[$functionId][$annotationVariable] = (
                        new RequestParamDescription(
                            \Psalm\Type::parseString($annotationType),
                            $annotationVariable,
                            $annotationDescription
                        )
                    );
                }
            }
        }
    }

    /**
     * @return array<string, RequestParamDescription>
     */
    private static function getDocBlockReturnTypes(
        string $functionId,
        \Psalm\Codebase $codebase
    ) {
        if (array_key_exists($functionId, self::$parsedMethodTypeMapping)) {
            return self::$parsedMethodTypeMapping[$functionId];
        }
        $methodId = new \Psalm\Internal\MethodIdentifier(
            ...explode('::', $functionId)
        );
        /** @psalm-suppress InternalMethod This code also appears in the examples, so it should be fine. */
        $methodStorage = $codebase->methods->getStorage($methodId);
        if (is_null($methodStorage->location)) {
            return [];
        }
        $statements = $codebase->getStatementsForFile(
            $methodStorage->location->file_path
        );
        $finder = new \PhpParser\NodeFinder();
        /** @var \PhpParser\Node\Stmt\ClassLike $classStmt */
        foreach (
            $finder->find(
                $statements,
                function (\PhpParser\Node $node): bool {
                    return $node instanceof \PhpParser\Node\Stmt\ClassLike;
                }
            ) as $classStmt
        ) {
            self::processClass(
                $classStmt,
                $methodId->fq_class_name
            );
        }
        if (!array_key_exists($functionId, self::$parsedMethodTypeMapping)) {
            return [];
        }
        return self::$parsedMethodTypeMapping[$functionId];
    }

    /**
     * @param  \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $expr
     * @param  \Psalm\FileManipulation[] $fileReplacements
     *
     * @return void
     */
    public static function afterMethodCallAnalysis(
        $expr,
        string $methodId,
        string $appearingMethodId,
        string $declaringMethodId,
        \Psalm\Context $context,
        \Psalm\StatementsSource $statementsSource,
        \Psalm\Codebase $codebase,
        array &$fileReplacements = [],
        \Psalm\Type\Union &$returnTypeCandidate = null
    ) {
        if (is_null($context->calling_function_id)) {
            // Not being called from within a function-like.
            return;
        }
        $functionId = strtolower($context->calling_function_id);
        if (!array_key_exists($functionId, self::$methodCallGraph)) {
            self::$methodCallGraph[$functionId] = [];
        }
        self::$methodCallGraph[$functionId][strtolower(
            $appearingMethodId
        )] = true;
    }

    /**
     * Called after a statement has been checked
     *
     * @param \Psalm\FileManipulation[] $fileReplacements
     *
     * @return null|false
     */
    public static function afterStatementAnalysis(
        \PhpParser\Node\Stmt\ClassLike $classStmt,
        \Psalm\Storage\ClassLikeStorage $classLikeStorage,
        \Psalm\StatementsSource $statementsSource,
        \Psalm\Codebase $codebase,
        array &$fileReplacements = []
    ) {
        if (is_null($classLikeStorage->location)) {
            return null;
        }

        // First go through all the methods in this class, parsing the doc
        // comment for each and saving its parsed representation to
        // self::$parsedMethodTypeMapping.
        self::processClass($classStmt, $classLikeStorage->name);

        $fileContents = $codebase->getFileContents(
            $classLikeStorage->location->file_name
        );
        foreach ($classStmt->stmts as $methodStmt) {
            if (!$methodStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $functionId = strtolower(
                "{$classLikeStorage->name}::{$methodStmt->name->name}"
            );

            $hasRequestArgument = false;
            foreach ($methodStmt->params as $param) {
                if ($param->type instanceof \PhpParser\Node\NullableType) {
                    $type = $param->type->type;
                } else {
                    $type = $param->type;
                }
                if (is_null($type)) {
                    continue;
                }
                if ($type->getAttribute('resolvedName') == 'OmegaUp\\Request') {
                    $hasRequestArgument = true;
                    break;
                }
            }

            $docblock = $methodStmt->getDocComment();
            $parsedDocComment = \Psalm\DocComment::parse(
                !is_null($docblock) ?
                $docblock->getText() :
                '/** */'
            );
            $docblockStart = (
                !is_null($docblock) ?
                $docblock->getFilePos() :
                intval(
                    $methodStmt->getAttribute(
                        'startFilePos'
                    )
                )
            );
            $docblockEnd = $functionStart = intval(
                $methodStmt->getAttribute(
                    'startFilePos'
                )
            );
            $precedingNewlinePos = strrpos(
                $fileContents,
                "\n",
                $docblockEnd - strlen($fileContents)
            );

            if ($precedingNewlinePos === false) {
                $indentation = '';
            } else {
                $firstLine = substr(
                    $fileContents,
                    $precedingNewlinePos + 1,
                    $docblockEnd - $precedingNewlinePos
                );
                $indentation = str_replace(ltrim($firstLine), '', $firstLine);
            }

            if (
                // Methods that do not have an \OmegaUp\Request argument don't
                // need the annotations.
                !$hasRequestArgument
            ) {
                $expected = [];
            } else {
                if (isset(self::$methodTypeMapping[$functionId])) {
                    $expected = self::$methodTypeMapping[$functionId];
                } else {
                    $expected = [];
                }

                // Now go through the callgraph, parsing any unvisited methods if needed.
                foreach (self::$methodCallGraph[$functionId] ?? [] as $calleeMethodId => $_) {
                    foreach (
                        self::getDocBlockReturnTypes(
                            $calleeMethodId,
                            $codebase
                        ) as $_ => $requestParam
                    ) {
                        if (array_key_exists($requestParam->name, $expected)) {
                            // TODO(lhchavez): Merge the descriptions and types.
                            continue;
                        }
                        $expected[$requestParam->name] = $requestParam;
                    }
                }
                ksort($expected);
            }
            $modified = false;
            $missing = [];
            foreach ($expected as $key => $_) {
                $missing[$key] = true;
            }
            foreach (self::$parsedMethodTypeMapping[$functionId] ?? [] as $_ => $requestParam) {
                if (!isset($expected[$requestParam->name])) {
                    // This is one property that is not expected and needs
                    // to be removed.
                    $modified = true;
                    continue;
                }
                unset($missing[$requestParam->name]);
                if (!is_null($requestParam->description)) {
                    $expected[$requestParam->name]->description = $requestParam->description;
                }
                if (
                    $expected[$requestParam->name]->type->isMixed() &&
                    $requestParam->type->isMixed()
                ) {
                    continue;
                }
                if (
                    !$expected[$requestParam->name]->type->isMixed() &&
                    $requestParam->type->isMixed()
                ) {
                    $modified = true;
                }
                // The type in the annotation is more specific than what we
                // found. Trust the annotator.
                $expected[$requestParam->name]->type = $requestParam->type;
            }

            if (!$modified && empty($missing)) {
                continue;
            }

            unset($parsedDocComment['specials']['omegaup-request-param']);
            if (!empty($expected)) {
                $parsedDocComment['specials'] = [
                    'omegaup-request-param' => array_map(
                        function (RequestParamDescription $description): string {
                            return strval($description);
                        },
                        array_values($expected)
                    ),
                ] + $parsedDocComment['specials'];
            }

            if ($codebase->alter_code) {
                $fileReplacements[] = new \Psalm\FileManipulation(
                    $docblockStart,
                    $docblockEnd,
                    \Psalm\DocComment::render($parsedDocComment, $indentation)
                );
                continue;
            }
            if (
                \Psalm\IssueBuffer::accepts(
                    new MismatchingDocblockOmegaUpRequestParamAnnotation(
                        (
                        'Mismatched dockblock annotations for ' .
                        "{$classLikeStorage->name}::{$methodStmt->name->name}: Wanted:\n\n" .
                        \Psalm\DocComment::render($parsedDocComment, '')
                        ),
                        new \Psalm\CodeLocation(
                            $statementsSource,
                            $methodStmt,
                            null,
                            true
                        )
                    ),
                    $statementsSource->getSuppressedIssues(),
                    true
                )
            ) {
                // do nothing
            }
        }
    }
}

class RequestParamDescription {
    /** @var \Psalm\Type\Union */
    public $type;

    /**
     * @var string
     * @readonly
     */
    public $name;

    /**
     * @var null|string
     */
    public $description;

    public function __construct(
        \Psalm\Type\Union $type,
        string $name,
        ?string $description = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;
    }

    public function __toString(): string {
        if (is_null($this->description)) {
            return "{$this->type} \${$this->name}";
        }
        return "{$this->type} \${$this->name} {$this->description}";
    }
}

class RequestAccessNotALiteralString extends \Psalm\Issue\PluginIssue {
}

class MismatchingDocblockOmegaUpRequestParamAnnotation extends \Psalm\Issue\PluginIssue {
}
