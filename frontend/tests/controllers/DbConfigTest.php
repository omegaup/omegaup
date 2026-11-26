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

        // The test database is created from schema.sql, which already has the
        // new columns. Remove them so the migration can be applied from scratch.
        foreach (
            [
            'ALTER TABLE `Emails` DROP INDEX `verification_id`;',
            'ALTER TABLE `Emails` DROP COLUMN `verification_expires_at`;',
            'ALTER TABLE `Emails` DROP COLUMN `verification_id`;',
            'ALTER TABLE `Emails` DROP COLUMN `verified`;',
            ] as $cleanupSql
        ) {
            try {
                $db->Execute($cleanupSql);
            } catch (\Exception $e) {
                // Ignore if column/index does not exist yet
            }
        }

        $cleanedSql = strval(
            preg_replace('/^--.*$/m', '', strval($migrationSql))
        );
        $statements = array_filter(
            array_map('trim', explode(';', $cleanedSql)),
            static fn (string $stmt): bool => $stmt !== ''
        );

        foreach ($statements as $stmt) {
            $db->Execute($stmt);
        }

        $now = \OmegaUp\Time::get();
        $db->Execute(
            'INSERT INTO `Users` (`username`, `password`, `verified`, `verification_id`, `main_email_id`) VALUES (?, ?, ?, ?, ?)',
            ['mig_verified_user', 'hash', 1, 'third_party_token', null]
        );
        $verifiedUserId = intval($db->Insert_ID());
        $db->Execute(
            'INSERT INTO `Emails` (`email`, `user_id`) VALUES (?, ?)',
            ['mig_verified_main@example.com', $verifiedUserId]
        );
        $verifiedEmailId = intval($db->Insert_ID());
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
        $unverifiedUserId = intval($db->Insert_ID());
        $db->Execute(
            'INSERT INTO `Emails` (`email`, `user_id`) VALUES (?, ?)',
            ['mig_unverified_main@example.com', $unverifiedUserId]
        );
        $unverifiedEmailId = intval($db->Insert_ID());
        $db->Execute(
            'UPDATE `Users` SET `main_email_id` = ? WHERE `user_id` = ?',
            [$unverifiedEmailId, $unverifiedUserId]
        );

        $db->Execute(
            'INSERT INTO `Users` (`username`, `password`, `verified`, `verification_id`, `main_email_id`) VALUES (?, ?, ?, ?, ?)',
            ['mig_deleted_user', 'hash', 0, null, null]
        );
        $deletedUserId = intval($db->Insert_ID());
        $db->Execute(
            'INSERT INTO `Emails` (`email`, `user_id`) VALUES (?, ?)',
            ['deleted_user_abc@example.com', $deletedUserId]
        );

        // Re-run the migration UPDATEs after seeding the test rows.
        foreach ($statements as $stmt) {
            if (stripos($stmt, 'UPDATE ') === 0) {
                $db->Execute($stmt);
            }
        }

        /** @var array{verified: int|string, verification_id: ?string} */
        $verifiedMainRow = $db->GetRow(
            'SELECT `verified`, `verification_id` FROM `Emails` WHERE `email` = ?',
            ['mig_verified_main@example.com']
        );
        $this->assertSame(1, intval($verifiedMainRow['verified']));
        $this->assertNull($verifiedMainRow['verification_id']);

        $secVerified = intval(
            $db->GetOne(
                'SELECT `verified` FROM `Emails` WHERE `email` = ?',
                ['mig_verified_sec@example.com']
            )
        );
        $this->assertSame(0, $secVerified);

        /** @var array{verified: int|string, verification_id: ?string, verification_expires_at: ?string} */
        $unverifiedMainRow = $db->GetRow(
            'SELECT `verified`, `verification_id`, `verification_expires_at` FROM `Emails` WHERE `email` = ?',
            ['mig_unverified_main@example.com']
        );
        $this->assertSame(0, intval($unverifiedMainRow['verified']));
        $this->assertSame(
            'pending_token_123',
            $unverifiedMainRow['verification_id']
        );
        $this->assertNotNull($unverifiedMainRow['verification_expires_at']);
        $this->assertGreaterThanOrEqual(
            $now,
            strtotime(strval($unverifiedMainRow['verification_expires_at']))
        );

        $deletedRow = $db->GetRow(
            'SELECT `verified`, `verification_id` FROM `Emails` WHERE `email` = ?',
            ['deleted_user_abc@example.com']
        );
        $this->assertSame(0, intval($deletedRow['verified']));
        $this->assertNull($deletedRow['verification_id']);

        // Cleanup test records. The new columns are kept so the test database
        // matches schema.sql.
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
    }
}
