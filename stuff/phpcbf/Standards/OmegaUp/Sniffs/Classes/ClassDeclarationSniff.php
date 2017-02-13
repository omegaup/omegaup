<?php

class OmegaUp_Sniffs_Classes_ClassDeclarationSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {
        return [
                T_NAMESPACE,
                T_CLASS,
                T_INTERFACE,
                T_TRAIT,
               ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param integer              $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $tokens    = $phpcsFile->getTokens();
        $errorData = [strtolower($tokens[$stackPtr]['content'])];

        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            $error = 'Possible parse error: %s missing opening or closing brace';
            $phpcsFile->addWarning($error, $stackPtr, 'MissingBrace', $errorData);
            return;
        }

        $curlyBrace  = $tokens[$stackPtr]['scope_opener'];
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($curlyBrace - 1), $stackPtr, true);
        $classLine   = $tokens[$lastContent]['line'];
        $braceLine   = $tokens[$curlyBrace]['line'];
        if ($braceLine !== $classLine) {
            $error = 'Opening brace of a %s must be on the same line as definition';
            $fix   = $phpcsFile->addFixableError($error, $curlyBrace, 'OpenBraceSameLine', $errorData);
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = $lastContent + 1; $i < $curlyBrace; $i++) {
                    if ($tokens[$i]['code'] === T_WHITESPACE) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                }
                $phpcsFile->fixer->addContentBefore($curlyBrace, ' ');
                $phpcsFile->fixer->endChangeset();
            }
        }

        $nextContent = $phpcsFile->findNext(T_WHITESPACE, $curlyBrace + 1, null, true);
        $nextLine  = $tokens[$nextContent]['line'];
        if ($braceLine + 1 !== $nextLine && $tokens[$stackPtr]['content']) {
            $error = 'Opening brace of a %s must not have empty lines after';
            $fix   = $phpcsFile->addFixableError($error, $curlyBrace, 'NoBlankLineAfter', $errorData);
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = $curlyBrace + 1; $i < $nextContent; $i++) {
                    if ($tokens[$i]['line'] === $braceLine) {
                        continue;
                    }
                    if ($tokens[$i]['line'] === $nextLine) {
                        break;
                    }
                    $phpcsFile->fixer->replaceToken($i, '');
                }
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
