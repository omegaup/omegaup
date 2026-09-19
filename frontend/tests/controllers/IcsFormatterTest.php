<?php

/**
 * Tests for IcsFormatter class
 * These tests verify RFC 5545 compliant iCalendar generation
 */

class IcsFormatterTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test basic ICS generation for a contest
     */
    public function testFormatContestBasic() {
        // Create a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contest = $contestData['contest'];
        $contestUrl = 'https://omegaup.com/arena/' . $contest->alias . '/';

        // Generate ICS content
        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Verify VCALENDAR structure
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsContent);
        $this->assertStringContainsString('END:VCALENDAR', $icsContent);
        $this->assertStringContainsString('VERSION:2.0', $icsContent);
        $this->assertStringContainsString(
            'PRODID:-//omegaUp//Contest Calendar//EN',
            $icsContent
        );
        $this->assertStringContainsString('CALSCALE:GREGORIAN', $icsContent);
        $this->assertStringContainsString('METHOD:PUBLISH', $icsContent);

        // Verify VEVENT structure
        $this->assertStringContainsString('BEGIN:VEVENT', $icsContent);
        $this->assertStringContainsString('END:VEVENT', $icsContent);
        $this->assertStringContainsString('DTSTART:', $icsContent);
        $this->assertStringContainsString('DTEND:', $icsContent);
        $this->assertStringContainsString('DTSTAMP:', $icsContent);
        $this->assertStringContainsString(
            'UID:contest-' . $contest->contest_id . '@omegaup.com',
            $icsContent
        );
        $this->assertStringContainsString('SEQUENCE:', $icsContent);
        $this->assertStringContainsString('LAST-MODIFIED:', $icsContent);
        $this->assertStringContainsString('STATUS:CONFIRMED', $icsContent);
        $this->assertStringContainsString('TRANSP:OPAQUE', $icsContent);

        // Verify URL is included
        $this->assertStringContainsString('URL:' . $contestUrl, $icsContent);

        // Verify line endings are CRLF
        $this->assertStringContainsString("\r\n", $icsContent);

        // Verify ends with CRLF
        $this->assertStringEndsWith("\r\n", $icsContent);
    }

    /**
     * Test timestamp formatting is in UTC format
     */
    public function testTimestampFormat() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contest = $contestData['contest'];
        $contestUrl = 'https://omegaup.com/arena/' . $contest->alias . '/';

        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Timestamp should be in YYYYMMDDTHHMMSSZ format
        // Match DTSTART with UTC timestamp format
        $this->assertMatchesRegularExpression(
            '/DTSTART:\d{8}T\d{6}Z/',
            $icsContent
        );
        $this->assertMatchesRegularExpression(
            '/DTEND:\d{8}T\d{6}Z/',
            $icsContent
        );
        $this->assertMatchesRegularExpression(
            '/DTSTAMP:\d{8}T\d{6}Z/',
            $icsContent
        );
    }

    /**
     * Test that special characters are properly escaped
     */
    public function testTextEscaping() {
        // Create a contest with special characters in title and description
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => 'Test; Contest, with\\special: chars',
                'description' => "Line1\nLine2\r\nLine3",
                'alias' => 'test-escaping-contest',
            ])
        );
        $contest = $contestData['contest'];
        $contestUrl = 'https://omegaup.com/arena/' . $contest->alias . '/';

        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Semicolons should be escaped
        $this->assertStringContainsString('\\;', $icsContent);
        // Commas should be escaped
        $this->assertStringContainsString('\\,', $icsContent);
        // Backslashes should be escaped (as \\)
        $this->assertStringContainsString('\\\\', $icsContent);
        // Newlines should be escaped as \n
        $this->assertStringContainsString('\\n', $icsContent);
    }

    /**
     * Test line folding for long lines
     */
    public function testLineFolding() {
        // Create a contest with a very long description that exceeds 75 octets
        $longDescription = str_repeat('a', 200);
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'description' => $longDescription,
            ])
        );
        $contest = $contestData['contest'];
        $contestUrl = 'https://omegaup.com/arena/' . $contest->alias . '/';

        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Lines should be folded with CRLF + space
        $this->assertStringContainsString("\r\n ", $icsContent);

        // Verify no line exceeds 75 octets (excluding CRLF)
        $lines = explode("\r\n", $icsContent);
        foreach ($lines as $line) {
            // Content lines should not exceed 75 bytes
            // Note: Continuation lines start with a space which counts
            $this->assertLessThanOrEqual(
                75,
                strlen($line),
                'Line exceeds 75 octets: ' . substr($line, 0, 80) . '...'
            );
        }
    }

    /**
     * Test line folding with multi-byte characters
     */
    public function testLineFoldingMultiByte() {
        // Create a contest with multi-byte characters (e.g., emoji, accented chars)
        $multiByteName = str_repeat('ñ', 50);  // Each ñ is 2 bytes in UTF-8
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => $multiByteName,
                'alias' => 'test-multibyte-contest',
            ])
        );
        $contest = $contestData['contest'];
        $contestUrl = 'https://omegaup.com/arena/' . $contest->alias . '/';

        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Verify the content is valid (no cut multi-byte characters)
        $this->assertNotFalse(
            mb_check_encoding($icsContent, 'UTF-8'),
            'ICS content should be valid UTF-8'
        );

        // Verify line length limits in bytes
        $lines = explode("\r\n", $icsContent);
        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(
                75,
                strlen($line),
                'Line exceeds 75 octets'
            );
        }
    }

    /**
     * Test SEQUENCE number computation
     */
    public function testSequenceNumber() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contest = $contestData['contest'];
        $contestUrl = 'https://omegaup.com/arena/' . $contest->alias . '/';

        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // SEQUENCE should be a non-negative integer
        $this->assertMatchesRegularExpression(
            '/SEQUENCE:\d+/',
            $icsContent
        );
    }

    /**
     * Test contest with null/empty optional fields
     */
    public function testContestWithNullFields() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contest = $contestData['contest'];
        // Set optional fields to null/empty
        $contest->title = '';
        $contest->description = '';
        $contestUrl = 'https://omegaup.com/arena/' . $contest->alias . '/';

        // Should not throw an exception
        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Should still have valid structure
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsContent);
        $this->assertStringContainsString('SUMMARY:', $icsContent);
        $this->assertStringContainsString('DESCRIPTION:', $icsContent);
    }

    /**
     * Test that short lines are not folded
     */
    public function testShortLinesNotFolded() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => 'Short',
                'description' => 'Brief',
            ])
        );
        $contest = $contestData['contest'];
        $contestUrl = 'https://omegaup.com/arena/x/';

        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Count fold markers - there should be minimal folding for short content
        // The URL line and some header lines shouldn't be folded
        $versionLine = 'VERSION:2.0';
        $this->assertStringContainsString($versionLine . "\r\n", $icsContent);
    }
}
