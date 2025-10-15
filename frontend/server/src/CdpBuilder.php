<?php

namespace OmegaUp;

use ZipArchive;

class CdpBuilder {


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

    public static function setProblemMarkdown(array &$cdp, string $markdown): void {
        if (!empty($cdp['problemMarkdown'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidSetProblemMarkdown',
                'statement'
            );
        }
        $cdp['problemMarkdown'] = $markdown;
    }

    public static function setSolutionMarkdown(array &$cdp, string $markdown): void {
        if (!empty($cdp['problemSolutionMarkdown'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidSetSolutionMarkdown',
                'solution'
            );
        }
        $cdp['problemSolutionMarkdown'] = $markdown;
    }

    public static function addCase(
        array &$cdp,
        string $groupName,
        string $caseName,
        string $extension,
        string $content
    ): void {
        \OmegaUp\Validators::validateZipCaseFileName($caseName,$extension);


        $cdp['__cases'][$groupName] ??= [];
        $cdp['__cases'][$groupName][$caseName] ??= [];
        $cdp['__cases'][$groupName][$caseName][$extension] = $content;
    }

    public static function setTestplan(array &$cdp, ?string $testplan): void {
        $cdp['__testplan'] = $testplan;
    }

    public static function buildCases(array &$cdp): void {
        $points = self::parseTestplan($cdp['__testplan']);
        $groups = self::buildCaseGroups($cdp['__cases'], $points);
        $cdp['casesStore'] = $groups;
    }

    public static function processImages(array &$cdp, ZipArchive $zip): void {
        $cdp['problemMarkdown'] = self::convertImagesToBase64(
            $cdp['problemMarkdown'],
            $zip
        );
    }

    public static function build(array &$cdp): array {
        if (empty($cdp['problemMarkdown'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'statement'
            );
        }
        unset($cdp['__cases'], $cdp['__testplan']);
        return $cdp;
    }

    private static function parseTestplan(?string $testplan): array {
        $points = [];

        if ($testplan === null) {
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

    private static function buildCaseGroups(array &$cases, array $points): array {
        $groups = [];

        foreach ($cases as $groupName => $casesInGroup) {
            $groupID = self::generate_uuid_v4();
            $group = [
                'groupID' => $groupID,
                'name' => $groupName === 'ungrouped' ? '' : $groupName,
                'points' => 100,
                'autoPoints' => true,
                'ungroupedCase' => $groupName === 'ungrouped',
                'cases' => []
            ];

            foreach ($casesInGroup as $caseName => $caseData) {
                if (!isset($caseData['in']) || !isset($caseData['out'])) {
                    continue;
                }

                $caseID = self::generate_uuid_v4();
                $lineID = self::generate_uuid_v4();
                
                $casePath = $groupName === 'ungrouped' ? $caseName : "$groupName.$caseName";
                $casePoints = $points[$casePath] ?? 100;

                $case = [
                    'caseID' => $caseID,
                    'groupID' => $groupID,
                    'lines' => [
                        [
                            'lineID' => $lineID,
                            'caseID' => $caseID,
                            'label' => '',
                            'data' => [
                                'kind' => 'multiline',
                                'value' => $caseData['in']
                            ]
                        ]
                    ],
                    'points' => $casePoints,
                    'autoPoints' => true,
                    'output' => $caseData['out'],
                    'name' => $caseName
                ];

                $group['cases'][] = $case;
            }

            $groups[] = $group;
        }

        $firstGroup = !empty($groups) ? $groups[0] : null;
        $firstCase = !empty($firstGroup['cases']) ? $firstGroup['cases'][0] : null;

        return [
            'groups' => $groups,
            'selected' => [
                'groupID' => !empty($firstGroup) ? $firstGroup['groupID'] : null,
                'caseID' => !empty($firstCase) ? $firstCase['caseID'] : null
            ],
            'layouts' => [],
            'hide' => false
        ];
    }

    private static function convertImagesToBase64(
        string $markdownContent,
        ZipArchive $zip
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
                    $fileContent = ZipFileProcessor::getFileContent($zip, $filePathInZip);
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

        $base64Appendix = implode("\n", $base64References);
        return "{$newMarkdown}\n\n{$base64Appendix}";
    }

    private static function generate_uuid_v4(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
}