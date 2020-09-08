<?php

namespace OmegaUp\Psalm;

class TranslationStringChecker implements
    \Psalm\Plugin\Hook\AfterAnalysisInterface,
    \Psalm\Plugin\Hook\AfterFileAnalysisInterface,
    \Psalm\Plugin\Hook\AfterExpressionAnalysisInterface,
    \Psalm\Plugin\Hook\AfterMethodCallAnalysisInterface {
    /**
     * A list of messages that are present in the base exception classes.
     */
    const EXCEPTION_MESSAGES = [
        'csrfException',
        'emailNotVerified',
        'errorWhileSendingMail',
        'generalError',
        'loginRequired',
        'problemDeployerFailed',
        'resourceNotFound',
        'unableToVerifyCaptcha',
        'userNotAllowed',
        'usernameOrPassIsWrong',
    ];

    /** @var list<string>|null */
    private static $allTranslationStrings = null;

    /**
     * The name of the directory where the translation strings are going to be
     * written to.
     *
     * @var string|null
     */
    private static $translationStringsDirname;

    /**
     * Called after analysis is complete
     *
     * @param array<string, list<\Psalm\Internal\Analyzer\IssueData>> $issues
     *
     * @return void
     */
    public static function afterAnalysis(
        \Psalm\Codebase $codebase,
        array $issues,
        array $build_info,
        \Psalm\SourceControl\SourceControlInfo $source_control_info = null
    ) {
        file_put_contents(
            self::getTranslationStringsDirname() . '/exceptions',
            implode("\n", self::EXCEPTION_MESSAGES) . "\n"
        );
        file_put_contents(
            self::getTranslationStringsDirname() . '/problem_deployer_errors',
            implode(
                "\n",
                array_values(
                    \OmegaUp\ProblemDeployer::ERROR_MAPPING
                )
            ) . "\n"
        );
        file_put_contents(
            self::getTranslationStringsDirname() . '/restricted_tags',
            implode(
                "\n",
                \OmegaUp\Controllers\Problem::RESTRICTED_TAG_NAMES
            ) . "\n"
        );
        file_put_contents(
            self::getTranslationStringsDirname() . '/allowed_tags',
            implode(
                "\n",
                \OmegaUp\Controllers\QualityNomination::ALLOWED_TAGS
            ) . "\n"
        );
        file_put_contents(
            self::getTranslationStringsDirname() . '/allowed_public_tags',
            implode(
                "\n",
                \OmegaUp\Controllers\QualityNomination::ALLOWED_PUBLIC_TAGS
            ) . "\n"
        );
        file_put_contents(
            self::getTranslationStringsDirname() . '/level_tags',
            implode(
                "\n",
                \OmegaUp\Controllers\QualityNomination::LEVEL_TAGS
            ) . "\n"
        );
    }

    /**
     * Called after file analysis is complete
     *
     * @return void
     */
    public static function afterAnalyzeFile(
        \Psalm\StatementsSource $statements_source,
        \Psalm\Context $file_context,
        \Psalm\Storage\FileStorage $file_storage,
        \Psalm\Codebase $codebase
    ) {
        if (
            !isset(
                $file_storage->custom_metadata['omegaup-translation-strings']
            ) ||
            !is_array(
                $file_storage->custom_metadata['omegaup-translation-strings']
            )
        ) {
            return;
        }
        file_put_contents(
            self::getTranslationStringsDirname() . '/' . str_replace(
                '/',
                '_',
                $statements_source->getFileName()
            ),
            implode(
                "\n",
                array_unique(
                    $file_storage->custom_metadata['omegaup-translation-strings']
                )
            ) . "\n"
        );
    }

    /**
     * Returns whether the provided class name is a class that receives a
     * translation string name as first parameter.
     */
    private static function isSupportedConstructor(
        \Psalm\Codebase $codebase,
        string $constructorClassName
    ): bool {
        if ($constructorClassName === 'omegaup\\translationstring') {
            // This is the class that indicates that this is a translation
            // string.
            return true;
        }
        if (strpos($constructorClassName, 'omegaup\\exceptions\\') !== 0) {
            // Not the constructor of an exception.
            return false;
        }
        if ($constructorClassName == 'omegaup\\exceptions\\databaseoperationexception') {
            // This one class does not use translation strings.
            return false;
        }
        return (
            $constructorClassName === 'omegaup\\exceptions\\apiexception' ||
            $codebase->classExtends(
                $constructorClassName,
                'omegaup\\exceptions\\apiexception'
            )
        );
    }

    /**
     * Called after a statement has been checked
     *
     * @param \Psalm\FileManipulation[] $file_replacements
     *
     * @return null|false
     */
    public static function afterExpressionAnalysis(
        \PhpParser\Node\Expr $expr,
        \Psalm\Context $context,
        \Psalm\StatementsSource $statements_source,
        \Psalm\Codebase $codebase,
        array &$file_replacements = []
    ) {
        if (!($expr instanceof \PhpParser\Node\Expr\New_)) {
            return;
        }
        if (!($expr->class instanceof \PhpParser\Node\Name\FullyQualified)) {
            // Not something we can reason about.
            return;
        }
        if (
            !self::isSupportedConstructor(
                $codebase,
                $expr->class->toLowerString()
            )
        ) {
            return;
        }
        if (empty($expr->args)) {
            // There is no first argument.
            return;
        }
        if (!($expr->args[0]->value instanceof \PhpParser\Node\Scalar\String_)) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotALiteralString(
                        'First argument to an Exception constructor not a literal string',
                        new \Psalm\CodeLocation($statements_source, $expr)
                    ),
                    $statements_source->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }
        $translationString = $expr->args[0]->value->value;
        if (!in_array($translationString, self::getAllTranslationStrings())) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotFound(
                        "Translation string '$translationString' not found",
                        new \Psalm\CodeLocation($statements_source, $expr)
                    ),
                    $statements_source->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }
        /** @psalm-suppress InternalMethod This should be okay */
        $file_storage = $codebase->file_storage_provider->get(
            $statements_source->getFilePath()
        );
        if (
            !isset(
                $file_storage->custom_metadata['omegaup-translation-strings']
            )
        ) {
            $file_storage->custom_metadata['omegaup-translation-strings'] = [];
        }
        /** @var list<string> $file_storage->custom_metadata['omegaup-translation-strings'] */
        $file_storage->custom_metadata['omegaup-translation-strings'][] = $translationString;
        return null;
    }

    /**
     * @param  \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $expr
     * @param  \Psalm\FileManipulation[] $file_replacements
     *
     * @return void
     */
    public static function afterMethodCallAnalysis(
        $expr,
        string $method_id,
        string $appearing_method_id,
        string $declaring_method_id,
        \Psalm\Context $context,
        \Psalm\StatementsSource $statements_source,
        \Psalm\Codebase $codebase,
        array &$file_replacements = [],
        \Psalm\Type\Union &$return_type_candidate = null
    ) {
        if ($method_id !== 'OmegaUp\\Translations::get') {
            return;
        }
        if (!($expr->args[0]->value instanceof \PhpParser\Node\Scalar\String_)) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotALiteralString(
                        'First argument to an Exception constructor not a literal string',
                        new \Psalm\CodeLocation($statements_source, $expr)
                    ),
                    $statements_source->getSuppressedIssues()
                )
            ) {
                // do nothing
            }
            return;
        }
        $translationString = $expr->args[0]->value->value;
        if (!in_array($translationString, self::getAllTranslationStrings())) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotFound(
                        "Translation string '$translationString' not found",
                        new \Psalm\CodeLocation($statements_source, $expr)
                    ),
                    $statements_source->getSuppressedIssues()
                )
            ) {
                // do nothing
            }
            return;
        }
        /** @psalm-suppress InternalMethod This should be okay */
        $file_storage = $codebase->file_storage_provider->get(
            $statements_source->getFilePath()
        );
        if (
            !isset(
                $file_storage->custom_metadata['omegaup-translation-strings']
            )
        ) {
            $file_storage->custom_metadata['omegaup-translation-strings'] = [];
        }
        /** @var list<string> $file_storage->custom_metadata['omegaup-translation-strings'] */
        $file_storage->custom_metadata['omegaup-translation-strings'][] = $translationString;
    }

    /**
     * Returns all the translation strings.
     *
     * @return list<string>
     */
    private static function getAllTranslationStrings(): array {
        if (is_null(self::$allTranslationStrings)) {
            $filename = __DIR__ . '/../../../templates/en.lang';
            $translationFileContents = [];
            foreach (explode("\n", file_get_contents($filename)) as $line) {
                if (preg_match('/^(\\w+)/', $line, $matches) !== 1) {
                    continue;
                }
                $translationFileContents[] = $matches[1];
            }
            self::$allTranslationStrings = $translationFileContents;
        }
        return self::$allTranslationStrings;
    }

    /**
     * Returns the name of the directory where the translation strings are
     * going to be written to.
     */
    private static function getTranslationStringsDirname(): string {
        if (is_null(self::$translationStringsDirname)) {
            self::$translationStringsDirname  = dirname(
                __DIR__,
                3
            ) . '/tests/runfiles/translation_strings';
            if (!is_dir(self::$translationStringsDirname)) {
                mkdir(
                    self::$translationStringsDirname, /*$mode=*/
                    0755, /*$recursive=*/
                    true
                );
            }
        }
        return self::$translationStringsDirname;
    }
}

class TranslationStringNotALiteralString extends \Psalm\Issue\PluginIssue {
}

class TranslationStringNotFound extends \Psalm\Issue\PluginIssue {
}
