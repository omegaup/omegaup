<?php

namespace OmegaUp;

/**
 * Utility class for generating RFC 5545 compliant iCalendar (.ics) files.
 *
 * @see https://tools.ietf.org/html/rfc5545
 */
class IcsFormatter
{
    private const PRODID = '-//omegaUp//Contest Calendar//EN';
    private const VERSION = '2.0';
    private const LINE_LENGTH = 75;

    /**
     * Generate an ICS-formatted string for a contest event.
     *
     * @param \OmegaUp\DAO\VO\Contests $contest The contest to format
     * @param string $contestUrl The full URL to the contest
     * @return string RFC 5545 compliant iCalendar content
     */
    public static function formatContest(
        \OmegaUp\DAO\VO\Contests $contest,
        string $contestUrl
    ): string {
        if (
            is_null($contest->contest_id) ||
            is_null($contest->alias) ||
            is_null($contest->title) ||
            is_null($contest->start_time) ||
            is_null($contest->finish_time)
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'contest'
            );
        }

        $lines = [];

        // VCALENDAR header
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:' . self::VERSION;
        $lines[] = 'PRODID:' . self::PRODID;
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';

        // VEVENT
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'DTSTART:' . self::formatTimestamp($contest->start_time);
        $lines[] = 'DTEND:' . self::formatTimestamp($contest->finish_time);
        $lines[] = 'DTSTAMP:' . self::formatTimestamp(
            new \OmegaUp\Timestamp(\OmegaUp\Time::get())
        );
        $lines[] = 'UID:contest-' . $contest->contest_id . '@omegaup.com';
        $lines[] = self::foldLine('SUMMARY:' . self::escapeText($contest->title));
        $lines[] = self::foldLine(
            'DESCRIPTION:' . self::escapeText(
                $contest->description ?? ''
            )
        );
        $lines[] = 'URL:' . $contestUrl;
        $lines[] = 'STATUS:CONFIRMED';
        $lines[] = 'TRANSP:OPAQUE';
        $lines[] = 'END:VEVENT';

        // VCALENDAR footer
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Format a Timestamp to ICS datetime format (UTC).
     *
     * @param \OmegaUp\Timestamp $timestamp The timestamp to format
     * @return string ICS-formatted datetime string (YYYYMMDDTHHmmssZ)
     */
    private static function formatTimestamp(\OmegaUp\Timestamp $timestamp): string
    {
        return gmdate('Ymd\THis\Z', $timestamp->time);
    }

    /**
     * Escape special characters in text according to RFC 5545.
     *
     * @param string $text The text to escape
     * @return string Escaped text
     */
    private static function escapeText(string $text): string
    {
        // Escape backslash first, then semicolon, comma, and newlines
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace("\r\n", '\\n', $text);
        $text = str_replace("\n", '\\n', $text);
        $text = str_replace("\r", '\\n', $text);

        return $text;
    }

    /**
     * Fold long lines according to RFC 5545 (max 75 octets per line).
     *
     * @param string $line The line to fold
     * @return string Folded line(s)
     */
    private static function foldLine(string $line): string
    {
        if (strlen($line) <= self::LINE_LENGTH) {
            return $line;
        }

        $result = '';
        $pos = 0;
        $len = strlen($line);

        while ($pos < $len) {
            if ($pos === 0) {
                // First line: max 75 characters
                $chunk = substr($line, 0, self::LINE_LENGTH);
                $result = $chunk;
                $pos = strlen($chunk);
            } else {
                // Continuation lines: max 74 characters (plus leading space)
                $chunk = substr($line, $pos, self::LINE_LENGTH - 1);
                $result .= "\r\n " . $chunk;
                $pos += strlen($chunk);
            }
        }

        return $result;
    }
}
