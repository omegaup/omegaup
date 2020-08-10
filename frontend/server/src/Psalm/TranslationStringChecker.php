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
        array $buildInfo,
        \Psalm\SourceControl\SourceControlInfo $sourceControlInfo = null
    ) {
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
        if (!($expr instanceof \PhpParser\Node\Expr\New_)) {
            return;
        }
        if (!($expr->class instanceof \PhpParser\Node\Name\FullyQualified)) {
            // Not something we can reason about.
            return;
        }
        $constructorClassName = $expr->class->toLowerString();
        if (strpos($constructorClassName, 'omegaup\\exceptions\\') !== 0) {
            // Not the constructor of an exception.
            return;
        }
        if ($constructorClassName == 'omegaup\\exceptions\\databaseoperationexception') {
            // This one class does not use translation strings.
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
                        new \Psalm\CodeLocation($statementsSource, $expr)
                    ),
                    $statementsSource->getSuppressedIssues()
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
                        new \Psalm\CodeLocation($statementsSource, $expr)
                    ),
                    $statementsSource->getSuppressedIssues()
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
        if ($methodId !== 'OmegaUp\\Translations::get') {
            return;
        }
        if (!($expr->args[0]->value instanceof \PhpParser\Node\Scalar\String_)) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotALiteralString(
                        'First argument to an Exception constructor not a literal string',
                        new \Psalm\CodeLocation($statementsSource, $expr)
                    ),
                    $statementsSource->getSuppressedIssues()
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
                        new \Psalm\CodeLocation($statementsSource, $expr)
                    ),
                    $statementsSource->getSuppressedIssues()
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
