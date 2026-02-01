<?php

namespace OmegaUp\DAO;

/**
 * SystemSettings Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\SystemSettings}.
 */
class SystemSettings extends \OmegaUp\DAO\Base\SystemSettings {
    /**
     * Get a system setting by key.
     *
     * @param string $key The setting key
     * @return \OmegaUp\DAO\VO\SystemSettings|null The setting object or null if not found
     */
    public static function getByKey(string $key): ?\OmegaUp\DAO\VO\SystemSettings {
        $sql = 'SELECT * FROM System_Settings WHERE setting_key = ? LIMIT 1;';
        /** @var array{created_at: \OmegaUp\Timestamp, setting_description: null|string, setting_id: int, setting_key: string, setting_value: null|string, updated_at: \OmegaUp\Timestamp}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$key]);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\SystemSettings($row);
    }

    /**
     * Get a boolean system setting value.
     *
     * @param string $key The setting key
     * @param bool $default Default value if setting not found
     * @return bool The boolean setting value
     */
    public static function getBooleanSetting(
        string $key,
        bool $default = true
    ): bool {
        $setting = self::getByKey($key);
        if (is_null($setting)) {
            return $default;
        }
        return intval($setting->setting_value) === 1;
    }

    /**
     * Set a boolean system setting value.
     *
     * @param string $key The setting key
     * @param bool $value The value to set
     * @return int Number of affected rows
     */
    public static function setBooleanSetting(
        string $key,
        bool $value
    ): int {
        $setting = self::getByKey($key);
        if (is_null($setting)) {
            $newSetting = new VO\SystemSettings([
                'setting_key' => $key,
                'setting_value' => $value ? '1' : '0',
                'setting_description' => '',
            ]);
            return self::create($newSetting);
        }
        $setting->setting_value = $value ? '1' : '0';
        return self::update($setting);
    }

    /**
     * Get a string system setting value.
     *
     * @param string $key The setting key
     * @param string $default Default value if setting not found
     * @return string The string setting value
     */
    public static function getStringSetting(
        string $key,
        string $default = ''
    ): string {
        $setting = self::getByKey($key);
        if (is_null($setting)) {
            return $default;
        }
        return strval($setting->setting_value);
    }

    /**
     * Set a string system setting value.
     *
     * @param string $key The setting key
     * @param string $value The value to set
     * @return int Number of affected rows
     */
    public static function setStringSetting(
        string $key,
        string $value
    ): int {
        $setting = self::getByKey($key);
        if (is_null($setting)) {
            $newSetting = new VO\SystemSettings([
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_description' => '',
            ]);
            return self::create($newSetting);
        }
        $setting->setting_value = $value;
        return self::update($setting);
    }
}
