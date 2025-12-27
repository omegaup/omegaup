<?php

namespace OmegaUp;

/**
 * Utility class for generating RFC 5545 compliant iCalendar (.ics) files.
 *
 * @see https://tools.ietf.org/html/rfc5545
 */
class IcsFormatter {
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
        if (is_null($contest->start_time) || is_null($contest->finish_time)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'contest_times'
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
        $lines[] = self::foldLine(
            'SUMMARY:' . self::escapeText(
                $contest->title
            )
        );
        $lines[] = self::foldLine(
            'DESCRIPTION:' . self::escapeText(
                $contest->description ?? ''
            )
        );
        $lines[] = self::foldLine('URL:' . $contestUrl);
        $lines[] = 'STATUS:CONFIRMED';
        $lines[] = 'TRANSP:OPAQUE';
        $lines[] = 'END:VEVENT';

        // VCALENDAR footer
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Format a timestamp for iCalendar (UTC format).
     *
     * @param \OmegaUp\Timestamp $timestamp
     * @return string Formatted timestamp in YYYYMMDDTHHMMSSZ format
     */
    private static function formatTimestamp(\OmegaUp\Timestamp $timestamp): string {
        return gmdate('Ymd\THis\Z', $timestamp->time);
    }

    /**
     * Escape text for iCalendar content.
     * Per RFC 5545, backslash, semicolon, comma, and newlines must be escaped.
     *
     * @param string $text
     * @return string Escaped text
     */
    private static function escapeText(string $text): string {
        // Escape backslashes first, then other special characters
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace("\r\n", '\\n', $text);
        $text = str_replace("\n", '\\n', $text);
        $text = str_replace("\r", '\\n', $text);

        return $text;
    }

    /**
     * Fold long lines per RFC 5545 (max 75 octets per line).
     * Continuation lines begin with a space.
     *
     * @param string $line
     * @return string Folded line
     */
    private static function foldLine(string $line): string {
        if (strlen($line) <= self::LINE_LENGTH) {
            return $line;
        }

        $result = '';
        $currentLine = '';
        // First line can use full LINE_LENGTH, continuation lines need 1 less
        // because the leading space counts toward the 75 octet limit
        $currentLimit = self::LINE_LENGTH;

        // Process character by character to handle multi-byte correctly
        $chars = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false) {
            return $line;
        }

        foreach ($chars as $char) {
            $charLen = strlen($char); // byte length
            $currentLineLen = strlen($currentLine); // byte length

            if ($currentLineLen + $charLen > $currentLimit) {
                if ($result !== '') {
                    $result .= "\r\n ";
                }
                $result .= $currentLine;
                $currentLine = $char;
                // After first line, continuation lines are limited to LINE_LENGTH - 1
                // because the leading space is part of the 75 octet limit
                $currentLimit = self::LINE_LENGTH - 1;
            } else {
                $currentLine .= $char;
            }
        }

        if ($currentLine !== '') {
            if ($result !== '') {
                $result .= "\r\n ";
            }
            $result .= $currentLine;
        }

        return $result;
    }
}
