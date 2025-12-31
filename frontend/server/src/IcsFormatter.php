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
        // Validate that contest has required time fields
        if (is_null($contest->start_time)) {
            throw new \InvalidArgumentException(
                'Contest is missing start_time, cannot generate ICS'
            );
        }
        if (is_null($contest->finish_time)) {
            throw new \InvalidArgumentException(
                'Contest is missing finish_time, cannot generate ICS'
            );
        }

        $startTime = $contest->start_time;
        $finishTime = $contest->finish_time;

        $lines = [];

        // VCALENDAR header
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:' . self::VERSION;
        $lines[] = 'PRODID:' . self::PRODID;
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';

        // VEVENT
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'DTSTART:' . self::formatTimestamp($startTime);
        $lines[] = 'DTEND:' . self::formatTimestamp($finishTime);
        $lines[] = 'DTSTAMP:' . self::formatTimestamp(
            new \OmegaUp\Timestamp(\OmegaUp\Time::get())
        );
        $lines[] = 'UID:' . self::generateUniqueId($contest);
        // SEQUENCE increments with each update - use last_updated timestamp
        // This allows calendar apps to detect event changes
        $lines[] = 'SEQUENCE:' . self::computeSequence($contest);
        // LAST-MODIFIED tells calendar apps when the event was last changed
        $lines[] = 'LAST-MODIFIED:' . self::formatTimestamp(
            $contest->last_updated ?? new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            )
        );
        $lines[] = self::foldLine(
            'SUMMARY:' . self::escapeText(
                $contest->title ?? ''
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
     * Generate a unique ID for the iCalendar event.
     * Uses contest_id if available, otherwise falls back to alias.
     * Throws an exception if neither is available to ensure unique UIDs.
     *
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @return string Unique ID in the format "contest-{identifier}@omegaup.com"
     * @throws \InvalidArgumentException if neither contest_id nor alias is available
     */
    private static function generateUniqueId(
        \OmegaUp\DAO\VO\Contests $contest
    ): string {
        // Prefer contest_id as it's the primary key
        if (!is_null($contest->contest_id) && $contest->contest_id > 0) {
            return 'contest-' . $contest->contest_id . '@omegaup.com';
        }

        // Fall back to alias which is also unique
        if (!is_null($contest->alias) && $contest->alias !== '') {
            return 'contest-' . $contest->alias . '@omegaup.com';
        }

        // Neither identifier is available - cannot generate a unique UID
        throw new \InvalidArgumentException(
            'Contest is missing both contest_id and alias, cannot generate unique UID for ICS'
        );
    }

    /**
     * Compute a SEQUENCE number for the event based on last_updated timestamp.
     * SEQUENCE must be an integer that increments when the event is modified.
     * Using the timestamp ensures it increases with each update.
     *
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @return int Sequence number
     */
    private static function computeSequence(\OmegaUp\DAO\VO\Contests $contest): int {
        if (is_null($contest->last_updated)) {
            return 0;
        }
        // Use timestamp modulo to keep the number reasonable
        // Max signed 32-bit int is 2147483647
        return intval($contest->last_updated->time % 2147483647);
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
