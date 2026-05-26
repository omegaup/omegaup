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

class CdpBuilder {
    /**
     * Initialize the CDP structure for a problem.
     *
     * @param string $problemName The name of the problem to create.
     * @return CDPRaw Base CDP structure with empty initial values.
     */
    public static function initialize(string $problemName): array {
        return [
            'problemName' => $problemName,
            'problemMarkdown' => '',
            'problemCodeContent' => '',
            'problemCodeExtension' => '',
            'problemSolutionMarkdown' => '',
            'casesStore' => [],
            '__cases' => [],
            '__testplan' => null
        ];
    }

    /**
     * Determines whether to override the markdown based on language preferences.
     *
     * @param ?string $currentLanguage Currently selected language
     * @param string $candidateLanguage Language of the candidate content.
     * @param string $languagePreference User's preferred language.
     *
     * @return bool
     */
    public static function shouldOverrideMarkdown(
        ?string $currentLanguage,
        string $candidateLanguage,
        string $languagePreference,
    ): bool {
        if (is_null($currentLanguage)) {
            return true;
        }

        if ($currentLanguage === $languagePreference) {
            return false;
        }

        if ($candidateLanguage === $languagePreference) {
            return true;
        }

        if (
            $currentLanguage !== \OmegaUp\Controllers\Problem::DEFAULT_LANGUAGE &&
            $candidateLanguage === \OmegaUp\Controllers\Problem::DEFAULT_LANGUAGE
        ) {
            return true;
        }
        return false;
    }

    /**
     * Set the problem statement markdown.
     *
     * @param CDPRaw $cdp Reference to the CDP to modify.
     * @param string $markdown Markdown content of the problem statement.
     * @param string $language Language code of this markdown (en|es|pt).
     * @param string $languagePreference Preferred language code.
     * @param ?string &$currentLanguage Currently selected language for the statement
     */
    public static function setProblemMarkdown(
        array &$cdp,
        string $markdown,
        string $language,
        string $languagePreference,
        ?string &$currentLanguage
    ): void {
        if (
            !self::shouldOverrideMarkdown(
                $currentLanguage,
                $language,
                $languagePreference
            )
        ) {
            return;
        }
        $cdp['problemMarkdown'] = $markdown;
        $currentLanguage = $language;
    }

    /**
     * Set the problem solution markdown.
     *
     * @param CDPRaw $cdp Reference to the CDP to modify.
     * @param string $markdown Markdown content of the problem solution.
     * @param string $language Language code of this markdown (en|es|pt).
     * @param string $languagePreference Preferred language code.
     * @param ?string &$currentLanguage Currently selected language for the solution
     */
    public static function setSolutionMarkdown(
        array &$cdp,
        string $markdown,
        string $language,
        string $languagePreference,
        ?string &$currentLanguage
    ): void {
        if (
            !self::shouldOverrideMarkdown(
                $currentLanguage,
                $language,
                $languagePreference
            )
        ) {
            return;
        }
        $cdp['problemSolutionMarkdown'] = $markdown;
        $currentLanguage = $language;
    }

    /**
     * Adds a test case to the CDP under a specific group.
     *
     * @param CDPRaw $cdp Reference to the CDP to modify.
     * @param string $groupName Name of the group the case belongs to.
     * @param string $caseName Name of the test case.
     * @param string $extension File extension for input/output (e.g., "in", "out").
     * @param string $content File content.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException  If the case file name or extension are invalid.
     */
    public static function addCase(
        array &$cdp,
        string $groupName,
        string $caseName,
        string $extension,
        string $content
    ): void {
        \OmegaUp\Validators::validateZipCaseFileName($caseName, $extension);

        $cdp['__cases'][$groupName] ??= [];
        $cdp['__cases'][$groupName][$caseName] ??= [];
        $cdp['__cases'][$groupName][$caseName][$extension] = $content;
    }

     /**
     * Sets the testplan associated with the test cases.
     *
     * @param CDPRaw $cdp Reference to the CDP to modify.
     * @param string|null $testplan Testplan content or null if not provided.
     */
    public static function setTestplan(array &$cdp, ?string $testplan): void {
        $cdp['__testplan'] = $testplan;
    }

    /**
     * Builds the complete structure of test cases within the CDP,
     * generating groups, cases, and assigning points according to the testplan.
     *
     * @param CDPRaw $cdp Reference to the CDP to modify.
     */
    public static function buildCases(array &$cdp): void {
        $points = self::parseTestplan($cdp['__testplan']);
        $groups = self::buildCaseGroups($cdp['__cases'], $points);
        $cdp['casesStore'] = $groups;
    }

    /**
     * Converts all image references in the Markdown to Base64 format
     * using files within a Zip archive.
     *
     * @param CDPRaw $cdp Reference to the CDP to modify.
     * @param \ZipArchive $zip Zip archive containing the images.
     */
    public static function processImages(array &$cdp, \ZipArchive $zip): void {
        $cdp['problemMarkdown'] = self::convertImagesToBase64(
            $cdp['problemMarkdown'],
            $zip
        );
    }

    /**
     * Build the CDP, removing internal properties
     * and returning the structure ready to use.
     *
     * @param CDPRaw $cdp Reference to the CDP to finalize.
     * @param-out CDP $cdp
     * @return CDP Final CDP structure.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException If the problem statement is empty.
     */
    public static function build(array &$cdp): array {
        if (empty($cdp['problemMarkdown'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'statement'
            );
        }
        unset($cdp['__cases'], $cdp['__testplan']);
        /**
         * @var CDP
         * @psalm-suppress ReferenceConstraintViolation
         */
        return $cdp;
    }

    /**
     * Parses the testplan content into an array of points per case.
     *
     * @param string|null $testplan Testplan content or null if not provided.
     * @return array<string,int> Associative array where the key is the case path and the value is the points.
     */
    private static function parseTestplan(?string $testplan): array {
        $points = [];

        if (is_null($testplan)) {
            return $points;
        }
        $lines = explode("\n", trim($testplan));
        foreach ($lines as $line) {
            $parts = explode(' ', $line);
            if (count($parts) === 2 && is_numeric($parts[1])) {
                $points[$parts[0]] = intval($parts[1]);
            }
        }

        return $points;
    }

    /**
     * Builds case groups with their respective cases from the internal CDP data.
     *
     * @param array<string, array<string, array<string, string>>> $cases Cases organized by group.
     * @param array<string,int> $points Points assigned for each case according to the testplan.
     * @return CDPCasesStore Structure of groups and cases ready for the CDP.
     */
    private static function buildCaseGroups(
        array &$cases,
        array $points
    ): array {
        $groups = [];
        $autoPoints = empty($points);

        /**
         * @psalm-param array{in: string, out: string} $caseData
         * @psalm-return CDPCase
         */
        $buildCase = function (
            string $groupID,
            string $groupName,
            string $caseName,
            array $caseData
        ) use (
            $points,
            $autoPoints
        ): array {
            $caseID = self::generate_uuid_v4();
            $lineID = self::generate_uuid_v4();

            $casePath = $groupName === 'ungrouped' ? $caseName : "$groupName.$caseName";
            $casePoints = $points[$casePath] ?? 100;

            /** @var string $input */
            $input = $caseData['in'];
            /** @var string $output */
            $output = $caseData['out'];

            return [
                'caseID' => $caseID,
                'groupID' => $groupID,
                'lines' => [
                    [
                        'lineID' => $lineID,
                        'caseID' => $caseID,
                        'label' => '',
                        'data' => [
                            'kind' => 'multiline',
                            'value' => $input
                        ]
                    ]
                ],
                'points' => $casePoints,
                'autoPoints' => $autoPoints,
                'output' => $output,
                'name' => strval($caseName)
            ];
        };

        foreach ($cases as $groupName => $casesInGroup) {
            uksort($casesInGroup, 'strnatcmp');
            if ($groupName === 'ungrouped') {
                foreach ($casesInGroup as $caseName => $caseData) {
                    if (!isset($caseData['in'], $caseData['out'])) {
                        continue;
                    }
                    $groupID = self::generate_uuid_v4();

                    $case = $buildCase(
                        $groupID,
                        $groupName,
                        $caseName,
                        $caseData
                    );

                    $groups[] = [
                        'groupID' => $groupID,
                        'name' => strval($caseName),
                        'points' => $case['points'],
                        'autoPoints' => $autoPoints,
                        'ungroupedCase' => true,
                        'cases' => [$case]
                    ];
                }
                continue;
            }

            $groupID = self::generate_uuid_v4();
            $group = [
                'groupID' => $groupID,
                'name' => strval($groupName),
                'points' => 100,
                'autoPoints' => $autoPoints,
                'ungroupedCase' => false,
                'cases' => []
            ];

            foreach ($casesInGroup as $caseName => $caseData) {
                if (!isset($caseData['in']) || !isset($caseData['out'])) {
                    continue;
                }
                $case = $buildCase(
                    $groupID,
                    $groupName,
                    $caseName,
                    $caseData
                );

                $group['cases'][] = $case;
            }

            $groups[] = $group;
        }

        return [
            'groups' => $groups,
            'selected' => [
                'groupID' => '00000000-0000-0000-0000-000000000000',
                'caseID' => '00000000-0000-0000-0000-000000000000'
            ],
            'layouts' => [],
            'hide' => false
        ];
    }

    /**
     * Replaces image references in Markdown with Base64-encoded images.
     *
     * @param string $markdownContent Original Markdown content.
     * @param \ZipArchive $zip Zip archive containing the images.
     * @return string Markdown with embedded Base64 images.
     */
    private static function convertImagesToBase64(
        string $markdownContent,
        \ZipArchive $zip
    ): string {
        $processedImages = [];
        $imageCounter = 0;
        $pattern = '/!\[([^\]]+)\]\(([^)]+)\)/';

        $newMarkdown = preg_replace_callback(
            $pattern,
            function ($matches) use (&$zip, &$processedImages, &$imageCounter) {
                $description = $matches[1];
                $filePathInZip = $matches[2];

                try {
                    $fileContent = ZipFileProcessor::getFileContent(
                        $zip,
                        $filePathInZip
                    );
                } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                    return $matches[0];
                }

                $base64String = base64_encode($fileContent);

                if (array_key_exists($base64String, $processedImages)) {
                    $imageId = $processedImages[$base64String]['id'];
                    return sprintf('![%s][%s]', $description, $imageId);
                }

                $imageCounter++;
                $extension = pathinfo($filePathInZip, PATHINFO_EXTENSION);
                $mimeType = "image/{$extension}";

                $processedImages[$base64String] = [
                    'id' => $imageCounter,
                    'data' => "data:{$mimeType};base64,{$base64String}"
                ];

                return sprintf('![%s][%s]', $description, $imageCounter);
            },
            $markdownContent
        );

        $base64References = [];
        foreach ($processedImages as $data) {
            $base64References[] = "[{$data['id']}]: {$data['data']}";
        }

        if (empty($base64References)) {
            return $newMarkdown;
        }

        $base64Appendix = implode("\n", $base64References);
        return "{$newMarkdown}\n\n{$base64Appendix}";
    }

    /**
     * Generates a random UUID version 4.
     *
     * @return string UUID v4 in standard format.
     */
    private static function generate_uuid_v4(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
