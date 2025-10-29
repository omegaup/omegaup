<?php

namespace OmegaUp;

use ZipArchive;

class ZipToCdpConverter{


    public static function convert(string $zipFilePath, string $problemName): array {
  
        \OmegaUp\Validators::validateZipFileSize($zipFilePath);
        \OmegaUp\Validators::validateZipIntegrity($zipFilePath);
        \OmegaUp\Validators::validateStringNonEmpty($problemName, 'problemName');

        // Procesar archivo
        $zip = new ZipArchive();
        $zip->open($zipFilePath);

        // Construir CDP
        $cdp = CdpBuilder::initialize($problemName);
        self::populateBuilder($cdp, $zip);
        CdpBuilder::buildCases($cdp);
        CdpBuilder::processImages($cdp, $zip);

        $zip->close();

        return [
            'status' => 'ok',
            'message' => 'El ZIP fue convertido correctamente a CDP.',
            'cdp' => CdpBuilder::build($cdp)
        ];

    }
    private static function populateBuilder(array &$cdp, ZipArchive $zip): void {
        $rgxStatement = '/^statements\/(en|es|pt)\.markdown$/';
        $rgxSolutions = '/^solutions\/(en|es|pt)\.markdown$/';

        ZipFileProcessor::iterateFiles(
            $zip, 
            function ($fileName) use (&$cdp, $rgxStatement, $rgxSolutions, $zip) {

                \OmegaUp\Validators::validateZipFilePath($fileName);

                if (preg_match($rgxStatement, $fileName)) {
                    $content = ZipFileProcessor::getFileContent($zip, $fileName);
                    CdpBuilder::setProblemMarkdown($cdp, $content);
                }

                if (preg_match($rgxSolutions, $fileName)) {
                    $content = ZipFileProcessor::getFileContent($zip, $fileName);
                    CdpBuilder::setSolutionMarkdown($cdp, $content);
                }

                if (str_starts_with($fileName, 'cases/')) {
                    $pathParts = pathinfo($fileName);

                    if (!isset($pathParts['extension']) || !isset($pathParts['filename'])) {
                        return;
                    }

                    $extension = $pathParts['extension'];
                    $filename = $pathParts['filename'];

                    try{
                        \OmegaUp\Validators::validateZipCaseFileName($filename,$extension);
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

                    CdpBuilder::addCase($cdp, $groupName, $caseName, $extension, $content);
                }

                if ($fileName === 'testplan') {
                    $content = ZipFileProcessor::getFileContent($zip, $fileName);
                    CdpBuilder::setTestplan($cdp, $content);
                }
            }
        );
    }
}
