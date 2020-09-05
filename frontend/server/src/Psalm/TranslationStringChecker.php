<?php

namespace OmegaUp\Psalm;

class TranslationStringChecker implements
    \Psalm\Plugin\Hook\AfterAnalysisInterface,
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
}

class TranslationStringNotALiteralString extends \Psalm\Issue\PluginIssue {
}

class TranslationStringNotFound extends \Psalm\Issue\PluginIssue {
}
