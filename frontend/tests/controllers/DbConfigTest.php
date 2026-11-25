<?php

class DbConfigTest extends \OmegaUp\Test\ControllerTestCase {
    public function testTimeSync() {
        /** @var \OmegaUp\Timestamp|null */
        $dbTime = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT NOW();'
        );

        $this->assertSame(\OmegaUp\Time::get(), $dbTime->time);
    }

    public function testPhpUtc() {
        $timezone = date_default_timezone_get();

        $this->assertSame('UTC', $timezone);
    }

    public function testDbUtc() {
        // Go to the DB

        /** @var null|string */
        $timediff = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            'SELECT TIMEDIFF(NOW(), CONVERT_TZ(NOW(), @@session.time_zone, "+00:00")) d;'
        );

        $this->assertSame('00:00:00', $timediff);
    }

    public function testEmailsVerificationMigration() {
        $db = \OmegaUp\MySQLConnection::getInstance();
        $migrationSql = file_get_contents(
            OMEGAUP_ROOT . '/database/00290_emails_verification.sql'
        );
        $this->assertNotFalse($migrationSql);

        // Clean any prior columns if re-running locally
        foreach ([
            'ALTER TABLE `Emails` DROP INDEX `verification_id`;',
            'ALTER TABLE `Emails` DROP COLUMN `verification_expires_at`;',
            'ALTER TABLE `Emails` DROP COLUMN `verification_id`;',
            'ALTER TABLE `Emails` DROP COLUMN `verified`;',
        ] as $cleanupSql) {
            try {
                $db->Execute($cleanupSql);
            } catch (\Exception $e) {
                // Ignore if column/index does not exist yet
            }
        }

        $statements = array_filter(
            array_map('trim', explode(';', (string) $migrationSql)),
            static fn (string $stmt): bool => $stmt !== '' && !str_starts_with(
                preg_replace('/^(--[^\n]*\n\s*)*/', '', $stmt) ?? '',
                '--'
            ) || str_contains($stmt, 'UPDATE ') || str_contains($stmt, 'ALTER TABLE ')
        );

        // Execute non-comment SQL statements cleanly
        $cleanedSql = preg_replace('/^--.*$/m', '', (string) $migrationSql);
        foreach (explode(';', (string) $cleanedSql) as $stmt) {
            $trimmed = trim($stmt);
            if ($trimmed !== '') {
                $db->Execute($trimmed);
            }
        }

        $now = \OmegaUp\Time::get();
        $db->Execute(
            'INSERT INTO `Users` (`username`, `password`, `verified`, `verification_id`, `main_email_id`) VALUES (?, ?, ?, ?, ?)',
            ['mig_verified_user', 'hash', 1, 'third_party_token', null]
        );
        $verifiedUserId = (int) $db->Insert_ID();
        $db->Execute(
            'INSERT INTO `Emails` (`email`, `user_id`) VALUES (?, ?)',
            ['mig_verified_main@example.com', $verifiedUserId]
        );
        $verifiedEmailId = (int) $db->Insert_ID();
        $db->Execute(
            'UPDATE `Users` SET `main_email_id` = ? WHERE `user_id` = ?',
            [$verifiedEmailId, $verifiedUserId]
        );
        $db->Execute(
            'INSERT INTO `Emails` (`email`, `user_id`) VALUES (?, ?)',
            ['mig_verified_sec@example.com', $verifiedUserId]
        );

        $db->Execute(
            'INSERT INTO `Users` (`username`, `password`, `verified`, `verification_id`, `main_email_id`) VALUES (?, ?, ?, ?, ?)',
            ['mig_unverified_user', 'hash', 0, 'pending_token_123', null]
        );
        $unverifiedUserId = (int) $db->Insert_ID();
        $db->Execute(
            'INSERT INTO `Emails` (`email`, `user_id`) VALUES (?, ?)',
            ['mig_unverified_main@example.com', $unverifiedUserId]
        );
        $unverifiedEmailId = (int) $db->Insert_ID();
        $db->Execute(
            'UPDATE `Users` SET `main_email_id` = ? WHERE `user_id` = ?',
            [$unverifiedEmailId, $unverifiedUserId]
        );

        $db->Execute(
            'INSERT INTO `Users` (`username`, `password`, `verified`, `verification_id`, `main_email_id`) VALUES (?, ?, ?, ?, ?)',
            ['mig_deleted_user', 'hash', 0, null, null]
        );
        $deletedUserId = (int) $db->Insert_ID();
        $db->Execute(
            'INSERT INTO `Emails` (`email`, `user_id`) VALUES (?, ?)',
            ['deleted_user_abc@example.com', $deletedUserId]
        );

        // Re-run migration UPDATEs after seeding test rows
        foreach (explode(';', (string) $cleanedSql) as $stmt) {
            $trimmed = trim($stmt);
            if (stripos($trimmed, 'UPDATE ') === 0) {
                $db->Execute($trimmed);
            }
        }

        /** @var array{verified: int|string, verification_id: ?string} */
        $verifiedMainRow = $db->GetRow(
            'SELECT `verified`, `verification_id` FROM `Emails` WHERE `email` = ?',
            ['mig_verified_main@example.com']
        );
        $this->assertSame(1, (int) $verifiedMainRow['verified']);
        $this->assertNull($verifiedMainRow['verification_id']);

        $secVerified = (int) $db->GetOne(
            'SELECT `verified` FROM `Emails` WHERE `email` = ?',
            ['mig_verified_sec@example.com']
        );
        $this->assertSame(0, $secVerified);

        /** @var array{verified: int|string, verification_id: ?string, verification_expires_at: ?string} */
        $unverifiedMainRow = $db->GetRow(
            'SELECT `verified`, `verification_id`, `verification_expires_at` FROM `Emails` WHERE `email` = ?',
            ['mig_unverified_main@example.com']
        );
        $this->assertSame(0, (int) $unverifiedMainRow['verified']);
        $this->assertSame('pending_token_123', $unverifiedMainRow['verification_id']);
        $this->assertNotNull($unverifiedMainRow['verification_expires_at']);
        $this->assertGreaterThanOrEqual($now, strtotime((string) $unverifiedMainRow['verification_expires_at']));

        $deletedRow = $db->GetRow(
            'SELECT `verified`, `verification_id` FROM `Emails` WHERE `email` = ?',
            ['deleted_user_abc@example.com']
        );
        $this->assertSame(0, (int) $deletedRow['verified']);
        $this->assertNull($deletedRow['verification_id']);

        // Cleanup test records and schema columns
        $db->Execute('DELETE FROM `Emails` WHERE `user_id` IN (?, ?, ?)', [
            $verifiedUserId,
            $unverifiedUserId,
            $deletedUserId,
        ]);
        $db->Execute('DELETE FROM `Users` WHERE `user_id` IN (?, ?, ?)', [
            $verifiedUserId,
            $unverifiedUserId,
            $deletedUserId,
        ]);
        $db->Execute('ALTER TABLE `Emails` DROP INDEX `verification_id`');
        $db->Execute('ALTER TABLE `Emails` DROP COLUMN `verification_expires_at`');
        $db->Execute('ALTER TABLE `Emails` DROP COLUMN `verification_id`');
        $db->Execute('ALTER TABLE `Emails` DROP COLUMN `verified`');
    }
}
