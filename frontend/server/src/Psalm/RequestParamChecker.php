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
     * A mapping of \OmegaUp\Request::ensureXxx() methods to the type that they
     * are enforcing the API parameter to be.
     */
    const ENSURE_TYPE_MAPPING = [
        'OmegaUp\\Request::ensurebool' => 'bool',
        'OmegaUp\\Request::ensureoptionalbool' => 'bool|null',
        'OmegaUp\\Request::ensureint' => 'int',
        'OmegaUp\\Request::ensureoptionalint' => 'int|null',
        'OmegaUp\\Request::ensurefloat' => 'float',
        'OmegaUp\\Request::ensureoptionalfloat' => 'float|null',
        'OmegaUp\\Request::ensurestring' => 'string',
        'OmegaUp\\Request::ensureoptionalstring' => 'string|null',
        'OmegaUp\\Request::ensuretimestamp' => '\\OmegaUp\\Timestamp',
        'OmegaUp\\Request::ensureoptionaltimestamp' => '\\OmegaUp\\Timestamp|null',
    ];

    /**
     * Registers the fact that $functionId expects a parameter of name
     * $parameterName to have the specified $type.
     *
     * If the method type mapping table already contains an entry for that
     * function/parameter combination, the type that is expected will be the
     * intersection of the previously defined type and the specified one. This
     * allows mixed parameters to be gradually narrowed down to more specific
     * types.
     */
    private static function processParameter(
        string $functionId,
        string $parameterName,
        \Psalm\Type\Union $type,
        \Psalm\Codebase $codebase
    ): void {
        if (!array_key_exists($functionId, self::$methodTypeMapping)) {
            self::$methodTypeMapping[$functionId] = [];
        }
        if (
            array_key_exists(
                $parameterName,
                self::$methodTypeMapping[$functionId]
            )
        ) {
            $previousType = self::$methodTypeMapping[$functionId][$parameterName]->type;
            $intersectedType = \Psalm\Type::intersectUnionTypes(
                $previousType,
                $type,
                $codebase
            );
            if (is_null($intersectedType)) {
                throw new \Exception(
                    'Unable to reconcile types ' .
                    strval($previousType) .
                    ' and ' .
                    strval($type) .
                    " for {$functionId}, parameter '{$parameterName}'"
                );
            }
            $type = $intersectedType;
        }
        self::$methodTypeMapping[$functionId][$parameterName] = new RequestParamDescription(
            $type,
            $parameterName
        );
    }

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
        if (!is_null($context->calling_function_id)) {
            $functionId = strtolower($context->calling_function_id);
        } elseif (!is_null($context->calling_method_id)) {
            $functionId = $context->calling_method_id;
        } else {
            throw new \Exception('Empty calling method/function id');
        }
        self::processParameter(
            $functionId,
            $expr->dim->value,
            \Psalm\Type::getMixed(),
            $codebase
        );
        return null;
    }

    /**
     * Called for every Request ensureEnum/ensureOptionalEnum.
     *
     * @param \Psalm\FileManipulation[] $fileReplacements
     *
     * @return null|false
     */
    private static function processRequestEnum(
        \PhpParser\Node\Expr\MethodCall $expr,
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

        /** @var \PhpParser\Node\Identifier $expr->name */
        $methodId = 'OmegaUp\\Request::' . strtolower($expr->name->name);

        if (
            $methodId !== 'OmegaUp\\Request::ensureenum' &&
            $methodId !== 'OmegaUp\\Request::ensureoptionalenum'
        ) {
            return null;
        }

        if (!is_null($context->calling_function_id)) {
            $functionId = strtolower($context->calling_function_id);
        } elseif (!is_null($context->calling_method_id)) {
            $functionId = $context->calling_method_id;
        } else {
            throw new \Exception('Empty calling method/function id');
        }

        if (count($expr->args) < 2) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new EnumMissingArguments(
                        "{$methodId}() missing some arguments",
                        new \Psalm\CodeLocation($statementsSource, $expr)
                    ),
                    $statementsSource->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }
        if (!$expr->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            if (
                // Methods within \OmegaUp\Request are exempt
                strpos($functionId, 'omegaup\\request::') !== 0 &&
                \Psalm\IssueBuffer::accepts(
                    new RequestAccessNotALiteralString(
                        "{$methodId}() argument not a literal string",
                        new \Psalm\CodeLocation($statementsSource, $expr)
                    ),
                    $statementsSource->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }

        $returnType = $statementsSource->getNodeTypeProvider()->getType(
            $expr
        );
        if (is_null($returnType)) {
            return null;
        }

        self::processParameter(
            $functionId,
            $expr->args[0]->value->value,
            $returnType,
            $codebase
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
        } elseif ($expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return self::processRequestEnum(
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

            $parsedDocComment = \Psalm\DocComment::parsePreservingLength(
                new \PhpParser\Comment\Doc(
                    strval($docblock->getText())
                )
            );
            if (isset($parsedDocComment->tags['omegaup-request-param'])) {
                foreach (
                    $parsedDocComment->tags['omegaup-request-param'] as $requestParam
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
                fn (\PhpParser\Node $node) => $node instanceof \PhpParser\Node\Stmt\ClassLike
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
        if (!is_null($context->calling_function_id)) {
            $functionId = strtolower($context->calling_function_id);
        } elseif (!is_null($context->calling_method_id)) {
            $functionId = $context->calling_method_id;
        } else {
            // Not being called from within a function-like.
            return;
        }
        if (array_key_exists($methodId, self::ENSURE_TYPE_MAPPING)) {
            if (!$expr->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
                if (
                    // Methods within \OmegaUp\Request are exempt
                    strpos($functionId, 'omegaup\\request::') !== 0 &&
                    \Psalm\IssueBuffer::accepts(
                        new RequestAccessNotALiteralString(
                            "{$methodId}() argument not a literal string",
                            new \Psalm\CodeLocation($statementsSource, $expr)
                        ),
                        $statementsSource->getSuppressedIssues()
                    )
                ) {
                    // do nothing
                }
                return;
            }
            self::processParameter(
                $functionId,
                $expr->args[0]->value->value,
                \Psalm\Type::parseString(self::ENSURE_TYPE_MAPPING[$methodId]),
                $codebase
            );
            return;
        }
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
            $parsedDocComment = \Psalm\DocComment::parsePreservingLength(
                new \PhpParser\Comment\Doc(
                    !is_null($docblock) ?
                    strval($docblock->getText()) :
                    '/** */'
                )
            );
            $docblockStart = (
                !is_null($docblock) ?
                $docblock->getStartFilePos() :
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
                    continue;
                }
                // The type in the annotation is more specific than what we
                // found. Trust the annotator.
                $expected[$requestParam->name]->type = $requestParam->type;
            }

            if (!$modified && empty($missing)) {
                continue;
            }

            unset($parsedDocComment->tags['omegaup-request-param']);
            if (!empty($expected)) {
                $parsedDocComment->tags = $parsedDocComment->tags + [
                    'omegaup-request-param' => array_map(
                        fn (RequestParamDescription $description) => strval(
                            $description
                        ),
                        array_values($expected)
                    ),
                ];
            }

            if ($codebase->alter_code) {
                $fileReplacements[] = new \Psalm\FileManipulation(
                    $docblockStart,
                    $docblockEnd,
                    $parsedDocComment->render($indentation)
                );
                continue;
            }
            if (
                \Psalm\IssueBuffer::accepts(
                    new MismatchingDocblockOmegaUpRequestParamAnnotation(
                        (
                        'Mismatched dockblock annotations for ' .
                        "{$classLikeStorage->name}::{$methodStmt->name->name}: Wanted:\n\n" .
                        $parsedDocComment->render('')
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
        $result = $this->stringifyType() . " \${$this->name}";
        if (!is_null($this->description)) {
            $result .= " {$this->description}";
        }
        return $result;
    }

    private function stringifyType(): string {
        if ($this->type->hasLiteralValue()) {
            $types = [];
            foreach ($this->type->getAtomicTypes() as $type) {
                if ($type instanceof \Psalm\Type\Atomic\TLiteralFloat) {
                    $types[] = strval($type->value);
                } elseif ($type instanceof \Psalm\Type\Atomic\TLiteralString) {
                    $types[] = "'" . strval($type->value) . "'";
                } elseif ($type instanceof \Psalm\Type\Atomic\TLiteralInt) {
                    $types[] = strval($type->value);
                } else {
                    $types[] = strval($type);
                }
            }
            sort($types);
            return implode('|', $types);
        }
        return strval($this->type);
    }
}

class EnumMissingArguments extends \Psalm\Issue\PluginIssue {
}

class MismatchingDocblockOmegaUpRequestParamAnnotation extends \Psalm\Issue\PluginIssue {
}

class RequestAccessNotALiteralString extends \Psalm\Issue\PluginIssue {
}
