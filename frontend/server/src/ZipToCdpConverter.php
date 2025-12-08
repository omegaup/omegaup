<?php

namespace OmegaUp;

/**
 * @psalm-type CDPLine=array{lineID: string,caseID: string, label: string, data: array{kind: 'line'|'multiline'|'array'|'matrix', value: string}}
 * @psalm-type CDPCase=array{caseID: string,groupID: string, lines: list<CDPLine>, points: int,autoPoints: bool,output: string,name: string}
 * @psalm-type CDPGroup=array{groupID: string,name: string,points: int,autoPoints: bool,ungroupedCase: bool,cases: list<CDPCase>}
 * @psalm-type CDPCasesStore=array{groups: list<CDPGroup>,selected: array{groupID: string|null, caseID: string|null},layouts: list<array<string, string>>,hide: bool}
 * @psalm-type CDP=array{problemName: string,problemMarkdown: string,problemCodeContent: string,problemCodeExtension: string, problemSolutionMarkdown: string, casesStore: CDPCasesStore}
 * @psalm-type CDPRaw=array{problemName: string,problemMarkdown: string,problemCodeContent: string,problemCodeExtension: string, problemSolutionMarkdown: string, casesStore: CDPCasesStore|array<empty, empty>, __cases: array<string, array<string, array<string,string>>>|array<empty, empty>, __testplan: string|null}
 */

class ZipToCdpConverter{
    /**
     * Converts the content of a ZIP file into the CDP data structure.
     *
     * @param string $zipFilePath Path to the ZIP file.
     * @param string $problemName The name of the problem.
     * @param string $languagePreference Preferred language code (en|es|pt).
     * @return CDP
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException If the ZIP is invalid or a validation fails.
     */
    public static function convert(
        string $zipFilePath,
        string $problemName,
        string $languagePreference = 'es'
    ): array {
        \OmegaUp\Validators::validateZipFileSize($zipFilePath);
        \OmegaUp\Validators::validateZipIntegrity($zipFilePath);

        $zip = new \ZipArchive();
        $zip->open($zipFilePath);

        /** @var CDPRaw $cdp */
        $cdp = CdpBuilder::initialize($problemName);
        self::populateBuilder($cdp, $zip, $languagePreference);
        CdpBuilder::buildCases($cdp);
        CdpBuilder::processImages($cdp, $zip);

        $zip->close();

        /** @var CDP */
        return CdpBuilder::build($cdp);
    }

    /**
     * Populates the CDP raw structure with content  (statements, solutions, cases, testplan) from the ZIP.
     *
     * @param CDPRaw $cdp Reference to the CDP structure being built.
     * @param \ZipArchive $zip Open ZIP archive.
     * @param string $languagePreference Preferred language code (en|es|pt).
     * @return void
     *
     */
    private static function populateBuilder(
        array &$cdp,
        \ZipArchive $zip,
        string $languagePreference
    ): void {
        $rgxStatement = '/^statements\/(en|es|pt)\.markdown$/';
        $rgxSolutions = '/^solutions\/(en|es|pt)\.markdown$/';

        $currentStatementLanguage = null;
        $currentSolutionLanguage = null;

        ZipFileProcessor::iterateFiles(
            $zip,
            /**
             * @param string $fileName
             */
            function (string $fileName) use (
                &$cdp,
                $rgxStatement,
                $rgxSolutions,
                $zip,
                $languagePreference,
                &$currentStatementLanguage,
                &$currentSolutionLanguage,
            ) {
                \OmegaUp\Validators::validateZipFilePath($fileName);

                if (preg_match($rgxStatement, $fileName, $matches)) {
                    $content = ZipFileProcessor::getFileContent(
                        $zip,
                        $fileName
                    );
                    $language = $matches[1];
                    CdpBuilder::setProblemMarkdown(
                        $cdp,
                        $content,
                        $language,
                        $languagePreference,
                        $currentStatementLanguage
                    );
                }

                if (preg_match($rgxSolutions, $fileName, $matches)) {
                    $content = ZipFileProcessor::getFileContent(
                        $zip,
                        $fileName
                    );
                    $language = $matches[1];
                    CdpBuilder::setSolutionMarkdown(
                        $cdp,
                        $content,
                        $language,
                        $languagePreference,
                        $currentSolutionLanguage
                    );
                }

                if (str_starts_with($fileName, 'cases/')) {
                    $pathParts = pathinfo($fileName);

                    if (
                        !isset(
                            $pathParts['extension']
                        ) || !isset(
                            $pathParts['filename']
                        )
                    ) {
                        return;
                    }

                    $extension = $pathParts['extension'];
                    $filename = $pathParts['filename'];

                    try {
                        \OmegaUp\Validators::validateZipCaseFileName(
                            $filename,
                            $extension
                        );
                    } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                        return;
                    }

                    $content = ZipFileProcessor::getFileContentWithLimit(
                        $zip,
                        $fileName,
                        \OmegaUp\Validators::ZIP_CASE_SIZE_LIMIT_BYTES
                    );

                    $parts = explode('.', $filename);
                    $groupName = count($parts) === 2 ? $parts[0] : 'ungrouped';
                    $caseName = count($parts) === 2 ? $parts[1] : $parts[0];

                    CdpBuilder::addCase(
                        $cdp,
                        $groupName,
                        $caseName,
                        $extension,
                        $content
                    );
                }

                if ($fileName === 'testplan') {
                    $content = ZipFileProcessor::getFileContent(
                        $zip,
                        $fileName
                    );
                    CdpBuilder::setTestplan($cdp, $content);
                }
            }
        );
    }
}
