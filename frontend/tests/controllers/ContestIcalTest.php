<?php

/**
 * ContestIcalTest
 *
 * Tests for the /api/contest/ical endpoint that generates iCalendar files.
 */

class ContestIcalTest extends \OmegaUp\Test\ControllerTestCase
{
    /**
     * Test that apiIcal returns a valid ICS file for an existing contest.
     */
    public function testGetIcalForExistingContest()
    {
        // Create a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
            ])
        );

        // Generate ICS content directly (simulating what the API does)
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $this->assertNotNull($contest);
        $this->assertNotNull($contest->contest_id);
        $this->assertNotNull($contest->start_time);
        $this->assertNotNull($contest->finish_time);

        // Test IcsFormatter directly
        $contestUrl = OMEGAUP_URL . '/arena/' . urlencode(
            $contestData['request']['alias']
        ) . '/';
        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Verify ICS format
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsContent);
        $this->assertStringContainsString('VERSION:2.0', $icsContent);
        $this->assertStringContainsString('BEGIN:VEVENT', $icsContent);
        $this->assertStringContainsString('END:VEVENT', $icsContent);
        $this->assertStringContainsString('END:VCALENDAR', $icsContent);

        // Verify contest details are in the ICS
        $this->assertStringContainsString(
            'SUMMARY:' . $contest->title,
            $icsContent
        );
        $this->assertStringContainsString(
            'UID:contest-' . $contest->contest_id . '@omegaup.com',
            $icsContent
        );
        $this->assertStringContainsString('URL:' . $contestUrl, $icsContent);

        // Verify date format (YYYYMMDDTHHmmssZ)
        $this->assertMatchesRegularExpression(
            '/DTSTART:\d{8}T\d{6}Z/',
            $icsContent
        );
        $this->assertMatchesRegularExpression(
            '/DTEND:\d{8}T\d{6}Z/',
            $icsContent
        );
    }

    /**
     * Test ICS generation for contest with special characters in title.
     */
    public function testIcsEscapesSpecialCharacters()
    {
        // Create a contest with special characters
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => 'Test Contest; with, special: chars',
                'admissionMode' => 'public',
            ])
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $contestUrl = OMEGAUP_URL . '/arena/' . urlencode(
            $contestData['request']['alias']
        ) . '/';
        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // Verify semicolons and commas are escaped
        $this->assertStringContainsString('\\;', $icsContent);
        $this->assertStringContainsString('\\,', $icsContent);
    }

    /**
     * Test that ICS uses correct RFC 5545 line endings.
     */
    public function testIcsUsesCorrectLineEndings()
    {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
            ])
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $contestUrl = OMEGAUP_URL . '/arena/' . urlencode(
            $contestData['request']['alias']
        ) . '/';
        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // RFC 5545 requires CRLF line endings
        $this->assertStringContainsString("\r\n", $icsContent);
    }

    /**
     * Test that timestamps are formatted in UTC.
     */
    public function testIcsTimestampsAreUtc()
    {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'admissionMode' => 'public',
            ])
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $contestUrl = OMEGAUP_URL . '/arena/' . urlencode(
            $contestData['request']['alias']
        ) . '/';
        $icsContent = \OmegaUp\IcsFormatter::formatContest(
            $contest,
            $contestUrl
        );

        // UTC timestamps end with 'Z'
        $this->assertMatchesRegularExpression(
            '/DTSTART:\d{8}T\d{6}Z/',
            $icsContent
        );
    }
}
