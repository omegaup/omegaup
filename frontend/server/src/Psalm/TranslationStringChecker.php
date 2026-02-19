<?php

namespace OmegaUp\Psalm;

class TranslationStringChecker implements
    \Psalm\Plugin\EventHandler\AfterAnalysisInterface,
    \Psalm\Plugin\EventHandler\AfterFileAnalysisInterface,
    \Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface,
    \Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface {
    /**
     * A list of messages that are present in the base exception classes.
     */
    const EXCEPTION_MESSAGES = [
        'apiTokenRateLimitExceeded',
        'csrfException',
        'emailNotVerified',
        'errorWhileSendingMail',
        'generalError',
        'loginRequired',
        'methodNotAllowed',
        'problemDeployerFailed',
        'resourceNotFound',
        'serviceUnavailableError',
        'unableToVerifyCaptcha',
        'userNotAllowed',
        'usernameOrPassIsWrong',
    ];

    /**
     * A list of messages that are present in other scripts.
     */
    const SCRIPTS_MESSAGES = [
        'coderOfTheMonthNotice',
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
        \Psalm\Plugin\EventHandler\Event\AfterAnalysisEvent $event,
    ): void {
        file_put_contents(
            self::getTranslationStringsDirname() . '/exceptions',
            implode("\n", self::EXCEPTION_MESSAGES) . "\n"
        );
        file_put_contents(
            self::getTranslationStringsDirname() . '/scripts',
            implode("\n", self::SCRIPTS_MESSAGES) . "\n"
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
     */
    public static function afterAnalyzeFile(
        \Psalm\Plugin\EventHandler\Event\AfterFileAnalysisEvent $event,
    ): void {
        if (
            !isset(
                $event->getFileStorage()->custom_metadata['omegaup-translation-strings']
            )
        ) {
            return;
        }

        /** @var scalar|list<string> $translationStrings */
        $translationStrings = $event->getFileStorage()->custom_metadata['omegaup-translation-strings'];
        if (!is_array($translationStrings)) {
            return;
        }
        file_put_contents(
            self::getTranslationStringsDirname() . '/' . str_replace(
                '/',
                '_',
                $event->getStatementsSource()->getFileName()
            ),
            implode("\n", array_unique($translationStrings)) . "\n"
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
     */
    public static function afterExpressionAnalysis(
        \Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent $event,
    ): ?bool {
        $expr = $event->getExpr();
        if (!($expr instanceof \PhpParser\Node\Expr\New_)) {
            return null;
        }
        if (!($expr->class instanceof \PhpParser\Node\Name\FullyQualified)) {
            // Not something we can reason about.
            return null;
        }
        if (
            !self::isSupportedConstructor(
                $event->getCodebase(),
                $expr->class->toLowerString()
            )
        ) {
            return null;
        }
        if (empty($expr->getArgs())) {
            // There is no first argument.
            return null;
        }
        $value = $expr->getArgs()[0]->value;
        if (!($value instanceof \PhpParser\Node\Scalar\String_)) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotALiteralString(
                        'First argument to an Exception constructor not a literal string',
                        new \Psalm\CodeLocation(
                            $event->getStatementsSource(),
                            $expr
                        )
                    ),
                    $event->getStatementsSource()->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }
        $translationString = $value->value;
        if (!in_array($translationString, self::getAllTranslationStrings())) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotFound(
                        "Translation string '$translationString' not found",
                        new \Psalm\CodeLocation(
                            $event->getStatementsSource(),
                            $expr
                        )
                    ),
                    $event->getStatementsSource()->getSuppressedIssues()
                )
            ) {
                return false;
            }
            return null;
        }
        /** @psalm-suppress InternalMethod This should be okay */
        $fileStorage = $event->getCodebase()->file_storage_provider->get(
            $event->getStatementsSource()->getFilePath()
        );
        if (
            !isset(
                $fileStorage->custom_metadata['omegaup-translation-strings']
            )
        ) {
            $fileStorage->custom_metadata['omegaup-translation-strings'] = [];
        }
        /** @var list<string> $fileStorage->custom_metadata['omegaup-translation-strings'] */
        $fileStorage->custom_metadata['omegaup-translation-strings'][] = $translationString;
        return null;
    }

    public static function afterMethodCallAnalysis(
        \Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent $event,
    ): void {
        if ($event->getMethodId() !== 'OmegaUp\\Translations::get') {
            return;
        }
        $value = $event->getExpr()->getArgs()[0]->value;
        if (!($value instanceof \PhpParser\Node\Scalar\String_)) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotALiteralString(
                        'First argument to an Exception constructor not a literal string',
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
        $translationString = $value->value;
        if (!in_array($translationString, self::getAllTranslationStrings())) {
            if (
                \Psalm\IssueBuffer::accepts(
                    new TranslationStringNotFound(
                        "Translation string '$translationString' not found",
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
        /** @psalm-suppress InternalMethod This should be okay */
        $fileStorage = $event->getCodebase()->file_storage_provider->get(
            $event->getStatementsSource()->getFilePath()
        );
        if (
            !isset(
                $fileStorage->custom_metadata['omegaup-translation-strings']
            )
        ) {
            $fileStorage->custom_metadata['omegaup-translation-strings'] = [];
        }
        /** @var list<string> $fileStorage->custom_metadata['omegaup-translation-strings'] */
        $fileStorage->custom_metadata['omegaup-translation-strings'][] = $translationString;
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
                    self::$translationStringsDirname,
                    permissions: 0755,
                    recursive: true,
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
