<?php

namespace OmegaUp;

class SystemSettings {
    /**
     * @return bool
     */
    public static function getBooleanSetting(
        string $settingKey,
        bool $default
    ): bool {
        try {
            /** @var null|string $value */
            $value = \OmegaUp\MySQLConnection::getInstance()->GetOne(
                'SELECT `setting_value` FROM `System_Settings` WHERE `setting_key` = ?',
                [$settingKey]
            );
        } catch (\Exception $e) {
            return $default;
        }

        if (is_null($value)) {
            return $default;
        }
        $filtered = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        if (!is_null($filtered)) {
            return $filtered;
        }
        if (is_numeric($value)) {
            return intval($value) !== 0;
        }
        return $default;
    }

    public static function setBooleanSetting(
        string $settingKey,
        bool $value
    ): void {
        try {
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                'INSERT INTO `System_Settings` (`setting_key`, `setting_value`) '
                . 'VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)',
                [$settingKey, $value ? '1' : '0']
            );
        } catch (\Exception $e) {
            // Ignore errors to avoid failing API endpoints if table is missing.
        }
    }
}
