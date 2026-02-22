<?php

namespace OmegaUp\Psalm;

class RequestParamChecker implements
    \Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface,
    \Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface,
    \Psalm\Plugin\EventHandler\AfterClassLikeAnalysisInterface {
    /**
     * @var array<string, array<string, RequestParamDescription>>
     */
    private static $methodTypeMapping = [];

    /**
     * @var array<string, array<string, RequestParamDescription>>
     */
    private static $parsedMethodTypeMapping = [];

    /**
     * @var array<lowercase-string, array<lowercase-string, true>>
     */
    private static $methodCallGraph = [];

    /**
     * A mapping of \OmegaUp\Request::ensureXxx() methods to the type that they
     * are enforcing the API parameter to be.
     */
    public const ENSURE_TYPE_MAPPING = [
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
     * A mapping of \OmegaUp\Validator::validateXxx() methods to the type that they
     * are enforcing the API parameter to be.
     */
    public const VALIDATOR_TYPE_MAPPING = [
        'OmegaUp\\Validators::validatenumber' => 'int',
        'OmegaUp\\Validators::validatenumberinrange' => 'int',
        'OmegaUp\\Validators::validateoptionalnumber' => 'int|null',
        'OmegaUp\\Validators::validateemail' => 'string',
        'OmegaUp\\Validators::validatestringnonempty' => 'string',
        'OmegaUp\\Validators::validatestringoflengthinrange' => 'string|null',
        'OmegaUp\\Validators::validateoptionalstringnonempty' => 'string|null',
        'OmegaUp\\Validators::validatevalidalias' => 'string|null',
        'OmegaUp\\Validators::validatevalidnamespacedalias' => 'string',
        'OmegaUp\\Validators::validatevalidusernameidentity' => 'string',
        'OmegaUp\\Validators::validatedate' => 'string',
        'OmegaUp\\Validators::validateoptionaldate' => 'string|null',
        'OmegaUp\\Validators::validatetimestampinrange' => '\\OmegaUp\\Timestamp',
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
     * @param \Psalm\FileManipulation[] $file_replacements
     *
     * @return null|false
     */
    private static function processRequestPropertyFetch(
        \PhpParser\Node\Expr\ArrayDimFetch $expr,
        \Psalm\Context $context,
        \Psalm\StatementsSource $statements_source,
        \Psalm\Codebase $codebase,
        array &$file_replacements = []
    ) {
        $varType = $statements_source->getNodeTypeProvider()->getType(
            $expr->var
        );
        if (is_null($varType)) {
            return null;
        }
        $foundRequest = false;
        foreach ($varType->getAtomicTypes() as $_typeName => $type) {
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
                        new \Psalm\CodeLocation($statements_source, $expr)
                    ),
                    $statements_source->getSuppressedIssues()
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
     * @param \Psalm\FileManipulation[] $file_replacements
     *
     * @return null|false
     */
    private static function processRequestEnum(
        \PhpParser\Node\Expr\MethodCall $expr,
        \Psalm\Context $context,
        \Psalm\StatementsSource $statements_source,
        \Psalm\Codebase $codebase,
        array &$file_replacements = []
    ) {
        $varType = $statements_source->getNodeTypeProvider()->getType(
            $expr->var
        );
        if (is_null($varType)) {
            return null;
        }
        $foundRequest = false;
        foreach ($varType->getAtomicTypes() as $_typeName => $type) {
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

        if (count($expr->getArgs()) < 2) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new EnumMissingArguments(
                        "{$methodId}() missing some arguments",
                        new \Psalm\CodeLocation($statements_source, $expr)
                    ),
                    $statements_source->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }
        $stringValue = $expr->getArgs()[0]->value;
        if (!($stringValue instanceof \PhpParser\Node\Scalar\String_)) {
            if (
                // Methods within \OmegaUp\Request are exempt
                strpos($functionId, 'omegaup\\request::') !== 0 &&
                \Psalm\IssueBuffer::accepts(
                    new RequestAccessNotALiteralString(
                        "{$methodId}() argument not a literal string",
                        new \Psalm\CodeLocation($statements_source, $expr)
                    ),
                    $statements_source->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }

        $returnType = $statements_source->getNodeTypeProvider()->getType(
            $expr
        );
        if (is_null($returnType)) {
            return null;
        }

        self::processParameter(
            $functionId,
            $stringValue->value,
            $returnType,
            $codebase
        );
        return null;
    }

    /**
     * Called after a statement has been checked
     *
     * @return null|false
     */
    public static function afterExpressionAnalysis(
        \Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent $event,
    ): ?bool {
        if (
            $event->getContext()->parent !== 'OmegaUp\\Controllers\\Controller' &&
            $event->getContext()->self !== 'OmegaUp\\Controllers\\Controller'
        ) {
            return null;
        }
        $expr = $event->getExpr();
        if ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $fileReplacements = $event->getFileReplacements();
            $result = self::processRequestPropertyFetch(
                $expr,
                $event->getContext(),
                $event->getStatementsSource(),
                $event->getCodebase(),
                $fileReplacements,
            );
            $event->setFileReplacements($fileReplacements);
            return $result;
        } elseif ($expr instanceof \PhpParser\Node\Expr\MethodCall) {
            $fileReplacements = $event->getFileReplacements();
            $result = self::processRequestEnum(
                $expr,
                $event->getContext(),
                $event->getStatementsSource(),
                $event->getCodebase(),
                $fileReplacements,
            );
            $event->setFileReplacements($fileReplacements);
            return $result;
        }

        return null;
    }

    private static function processClass(
        \PhpParser\Node\Stmt\ClassLike $class_stmt,
        string $className
    ): void {
        foreach ($class_stmt->stmts as $methodStmt) {
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
     * @param lowercase-string $functionId
     *
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
        /** @var \PhpParser\Node\Stmt\ClassLike $class_stmt */
        foreach (
            $finder->find(
                $statements,
                fn (\PhpParser\Node $node) => $node instanceof \PhpParser\Node\Stmt\ClassLike
            ) as $class_stmt
        ) {
            self::processClass(
                $class_stmt,
                $methodId->fq_class_name
            );
        }
        if (!array_key_exists($functionId, self::$parsedMethodTypeMapping)) {
            return [];
        }
        return self::$parsedMethodTypeMapping[$functionId];
    }

    public static function afterMethodCallAnalysis(
        \Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent $event,
    ): void {
        $callingFunctionId = $event->getContext()->calling_function_id;
        $callingMethodId = $event->getContext()->calling_method_id;
        if (!is_null($callingFunctionId)) {
            $functionId = strtolower($callingFunctionId);
        } elseif (!is_null($callingMethodId)) {
            $functionId = $callingMethodId;
        } else {
            // Not being called from within a function-like.
            return;
        }
        if (
            array_key_exists(
                $event->getMethodId(),
                self::ENSURE_TYPE_MAPPING
            )
        ) {
            $stringValue = $event->getExpr()->getArgs()[0]->value;
            if (!$stringValue instanceof \PhpParser\Node\Scalar\String_) {
                if (
                    // Methods within \OmegaUp\Request are exempt
                    strpos($functionId, 'omegaup\\request::') !== 0 &&
                    \Psalm\IssueBuffer::accepts(
                        new RequestAccessNotALiteralString(
                            "{$event->getMethodId()}() argument not a literal string",
                            new \Psalm\CodeLocation(
                                $event->getStatementsSource(),
                                $event->getExpr()
                            )
                        ),
                        $event->getStatementsSource()->getSuppressedIssues()
                    )
                ) {
                    // do nothing
                }
                return;
            }
            self::processParameter(
                $functionId,
                $stringValue->value,
                \Psalm\Type::parseString(
                    self::ENSURE_TYPE_MAPPING[$event->getMethodId()]
                ),
                $event->getCodebase()
            );
            return;
        }
        if (
            array_key_exists(
                $event->getMethodId(),
                self::VALIDATOR_TYPE_MAPPING
            )
        ) {
            $stringValue = $event->getExpr()->getArgs()[1]->value;
            if (!$stringValue instanceof \PhpParser\Node\Scalar\String_) {
                if (
                    // Methods within \OmegaUp\Request or \OmegaUp\Validators are exempt
                    strpos($functionId, 'omegaup\\request::') !== 0 &&
                    strpos($functionId, 'omegaup\\validators::') !== 0 &&
                    \Psalm\IssueBuffer::accepts(
                        new RequestAccessNotALiteralString(
                            "{$event->getMethodId()}() second argument not a literal string",
                            new \Psalm\CodeLocation(
                                $event->getStatementsSource(),
                                $event->getExpr()
                            )
                        ),
                        $event->getStatementsSource()->getSuppressedIssues()
                    )
                ) {
                    // do nothing
                }
                return;
            }
            $value = $event->getExpr()->getArgs()[0]->value;
            if (
                !$value instanceof \PhpParser\Node\Expr\ArrayDimFetch ||
                !$value->var instanceof \PhpParser\Node\Expr\Variable ||
                $value->var->name != 'r'
            ) {
                // This is not a Request access.
                return;
            }
            if (!$value->dim instanceof \PhpParser\Node\Scalar\String_) {
                if (
                    // Methods within \OmegaUp\Request or \OmegaUp\Validators are exempt
                    strpos($functionId, 'omegaup\\request::') !== 0 &&
                    strpos($functionId, 'omegaup\\validators::') !== 0 &&
                    \Psalm\IssueBuffer::accepts(
                        new RequestAccessNotALiteralString(
                            "{$event->getMethodId()}() second argument not a literal string",
                            new \Psalm\CodeLocation(
                                $event->getStatementsSource(),
                                $event->getExpr()
                            )
                        ),
                        $event->getStatementsSource()->getSuppressedIssues()
                    )
                ) {
                    // do nothing
                }
                return;
            }
            if ($value->dim->value != $stringValue->value) {
                if (
                    \Psalm\IssueBuffer::accepts(
                        new RequestAccessNotALiteralString(
                            "{$event->getMethodId()}() second argument and \$r[] argument do not match",
                            new \Psalm\CodeLocation(
                                $event->getStatementsSource(),
                                $event->getExpr()
                            )
                        ),
                        $event->getStatementsSource()->getSuppressedIssues()
                    )
                ) {
                    // do nothing
                }
                return;
            }
            self::processParameter(
                $functionId,
                $stringValue->value,
                \Psalm\Type::parseString(
                    self::VALIDATOR_TYPE_MAPPING[$event->getMethodId()]
                ),
                $event->getCodebase()
            );
            return;
        }
        if (!array_key_exists($functionId, self::$methodCallGraph)) {
            self::$methodCallGraph[$functionId] = [];
        }
        self::$methodCallGraph[$functionId][strtolower(
            $event->getAppearingMethodId()
        )] = true;
    }

    /**
     * Called after a statement has been checked
     */
    public static function afterStatementAnalysis(
        \Psalm\Plugin\EventHandler\Event\AfterClassLikeAnalysisEvent $event
    ): ?bool {
        if (is_null($event->getClasslikeStorage()->location)) {
            return null;
        }

        // First go through all the methods in this class, parsing the doc
        // comment for each and saving its parsed representation to
        // self::$parsedMethodTypeMapping.
        self::processClass(
            $event->getStmt(),
            $event->getClasslikeStorage()->name
        );

        $classlikeStorageLocation = $event->getClasslikeStorage()->location;
        if (is_null($classlikeStorageLocation)) {
            throw new \Exception(
                'Unable to get location for  ' .
                strval($event->getClasslikeStorage())
            );
        }
        $fileContents = $event->getCodebase()->getFileContents(
            $classlikeStorageLocation->file_name
        );
        $fileReplacements = $event->getFileReplacements();
        foreach ($event->getStmt()->stmts as $methodStmt) {
            if (!$methodStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $functionId = strtolower(
                "{$event->getClasslikeStorage()->name}::{$methodStmt->name->name}"
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
                $expected = self::getMethodTypeMapping($functionId);

                // Now go through the callgraph, parsing any unvisited methods
                // if needed.
                foreach (
                    self::getMethodCalls(
                        $functionId
                    ) as $calleeMethodId => $_
                ) {
                    foreach (
                        self::getDocBlockReturnTypes(
                            $calleeMethodId,
                            $event->getCodebase()
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

            if ($event->getCodebase()->alter_code) {
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
                        "{$event->getClasslikeStorage()->name}::{$methodStmt->name->name}: Wanted:\n\n" .
                        $parsedDocComment->render('')
                        ),
                        new \Psalm\CodeLocation(
                            $event->getStatementsSource(),
                            $methodStmt,
                            null,
                            true
                        )
                    ),
                    $event->getStatementsSource()->getSuppressedIssues(),
                    true
                )
            ) {
                // do nothing
            }
        }
        $event->setFileReplacements($fileReplacements);
        return null;
    }

    /**
     * @return array<lowercase-string, true>
     */
    private static function getMethodCalls(string $functionId): array {
        $config = \Psalm\Config::getInstance();
        $rootCacheDirectory = $config->getCacheDirectory();
        if (!$rootCacheDirectory) {
            throw new \UnexpectedValueException('No cache directory defined');
        }
        $cacheDir = (
            $rootCacheDirectory . DIRECTORY_SEPARATOR
            . 'omegaup-callgraph'
        );
        $cachePath = (
            $cacheDir . DIRECTORY_SEPARATOR
            . sha1($functionId)
        );
        if ($config->use_igbinary) {
            $cachePath .= '-igbinary';
        }
        /** @var array<lowercase-string, true> */
        $methodCalls = [];
        if (array_key_exists($functionId, self::$methodCallGraph)) {
            // Even though the class was analyzed, the individual
            // methods calls inside the function might not have been.
            // Let's see if we had cached this function's callgraph before.
            $methodCalls = self::$methodCallGraph[$functionId];
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, permissions: 0755, recursive: true);
            }
            if ($config->use_igbinary) {
                file_put_contents(
                    $cachePath,
                    \igbinary_serialize($methodCalls),
                    LOCK_EX
                );
            } else {
                file_put_contents(
                    $cachePath,
                    \serialize($methodCalls),
                    LOCK_EX
                );
            }
        } elseif (is_file($cachePath)) {
            $contents = file_get_contents($cachePath);
            if ($contents !== false) {
                if ($config->use_igbinary) {
                    /** @var array<lowercase-string, true> */
                    $methodCalls = \igbinary_unserialize($contents);
                } else {
                    /** @var array<lowercase-string, true> */
                    $methodCalls = \unserialize($contents);
                }
            }
        }
        return $methodCalls;
    }

    /**
     * @return array<string, RequestParamDescription>
     */
    private static function getMethodTypeMapping(string $functionId): array {
        $config = \Psalm\Config::getInstance();
        $rootCacheDirectory = $config->getCacheDirectory();
        if (!$rootCacheDirectory) {
            throw new \UnexpectedValueException('No cache directory defined');
        }
        $cacheDir = (
            $rootCacheDirectory . DIRECTORY_SEPARATOR
            . 'omegaup-methodtypemapping'
        );
        $cachePath = (
            $cacheDir . DIRECTORY_SEPARATOR
            . sha1($functionId)
        );
        if ($config->use_igbinary) {
            $cachePath .= '-igbinary';
        }
        /** @var array<string, RequestParamDescription> */
        $methodTypeMapping = [];
        if (array_key_exists($functionId, self::$methodTypeMapping)) {
            // Even though the class was analyzed, the individual
            // methods calls inside the function might not have been.
            // Let's see if we had cached this function's callgraph before.
            $methodTypeMapping = self::$methodTypeMapping[$functionId];
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, permissions: 0755, recursive: true);
            }
            if ($config->use_igbinary) {
                file_put_contents(
                    $cachePath,
                    \igbinary_serialize($methodTypeMapping),
                    LOCK_EX
                );
            } else {
                file_put_contents(
                    $cachePath,
                    \serialize($methodTypeMapping),
                    LOCK_EX
                );
            }
        } elseif (is_file($cachePath)) {
            $contents = file_get_contents($cachePath);
            if ($contents !== false) {
                if ($config->use_igbinary) {
                    /** @var array<string, RequestParamDescription> */
                    $methodTypeMapping = \igbinary_unserialize($contents);
                } else {
                    /** @var array<string, RequestParamDescription> */
                    $methodTypeMapping = \unserialize($contents);
                }
            }
        }
        return $methodTypeMapping;
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
