<?php

/**
 * Tests for the \OmegaUp\DAO\SystemSettings data access object.
 */
class SystemSettingsDAOTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        \OmegaUp\DAO\SystemSettings::invalidateCache(
            'ephemeral_grader_enabled'
        );
    }

    public function testGetByKeyReturnsSeededSetting() {
        $setting = \OmegaUp\DAO\SystemSettings::getByKey(
            'ephemeral_grader_enabled'
        );

        $this->assertNotNull($setting);
        $this->assertSame('ephemeral_grader_enabled', $setting->setting_key);
        $this->assertSame('1', $setting->setting_value);
    }

    public function testGetByKeyReturnsNullForUnknownKey() {
        $this->assertNull(
            \OmegaUp\DAO\SystemSettings::getByKey(
                \OmegaUp\Test\Utils::createRandomString()
            )
        );
    }

    public function testGetBooleanSettingReadsSeededValue() {
        $this->assertTrue(
            \OmegaUp\DAO\SystemSettings::getBooleanSetting(
                'ephemeral_grader_enabled'
            )
        );
    }

    public function testGetBooleanSettingReturnsDefaultWhenMissing() {
        $key = \OmegaUp\Test\Utils::createRandomString();

        $this->assertFalse(
            \OmegaUp\DAO\SystemSettings::getBooleanSetting($key, false)
        );
        $this->assertTrue(
            \OmegaUp\DAO\SystemSettings::getBooleanSetting($key, true)
        );
    }

    public function testSetBooleanSettingCreatesThenUpdates() {
        $key = \OmegaUp\Test\Utils::createRandomString();

        \OmegaUp\DAO\SystemSettings::setBooleanSetting($key, true);
        $this->assertTrue(
            \OmegaUp\DAO\SystemSettings::getBooleanSetting($key, false)
        );

        \OmegaUp\DAO\SystemSettings::setBooleanSetting($key, false);
        $this->assertFalse(
            \OmegaUp\DAO\SystemSettings::getBooleanSetting($key, true)
        );
    }

    public function testGetStringSettingReturnsDefaultWhenMissing() {
        $key = \OmegaUp\Test\Utils::createRandomString();

        $this->assertSame(
            'fallback',
            \OmegaUp\DAO\SystemSettings::getStringSetting($key, 'fallback')
        );
    }

    public function testSetStringSettingCreatesThenUpdates() {
        $key = \OmegaUp\Test\Utils::createRandomString();

        \OmegaUp\DAO\SystemSettings::setStringSetting($key, 'first');
        $this->assertSame(
            'first',
            \OmegaUp\DAO\SystemSettings::getStringSetting($key)
        );

        \OmegaUp\DAO\SystemSettings::setStringSetting($key, 'second');
        $this->assertSame(
            'second',
            \OmegaUp\DAO\SystemSettings::getStringSetting($key)
        );
    }
}
