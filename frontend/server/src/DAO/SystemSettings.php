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
        $fields = join(
            ', ',
            array_keys(
                \OmegaUp\DAO\VO\SystemSettings::FIELD_NAMES
            )
        );
        $sql = "SELECT {$fields} FROM System_Settings WHERE setting_key = ? LIMIT 1;";
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
        $cache = new \OmegaUp\Cache(\OmegaUp\Cache::SYSTEM_SETTINGS, $key);
        $cached = $cache->get();
        if (!is_null($cached)) {
            return intval($cached) === 1;
        }
        $setting = self::getByKey($key);
        if (is_null($setting)) {
            return $default;
        }
        $value = intval($setting->setting_value) === 1;
        // Store as int because Cache::get() treats a stored `false` as a miss.
        $cache->set($value ? 1 : 0, timeout: 0);
        return $value;
    }

    /**
     * Drop the cached value for a setting so the next read hits the database.
     *
     * @param string $key The setting key
     */
    public static function invalidateCache(string $key): void {
        (new \OmegaUp\Cache(\OmegaUp\Cache::SYSTEM_SETTINGS, $key))->delete();
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
            $newSetting = new \OmegaUp\DAO\VO\SystemSettings([
                'setting_key' => $key,
                'setting_value' => $value ? '1' : '0',
                'setting_description' => '',
            ]);
            $affectedRows = self::create($newSetting);
        } else {
            $setting->setting_value = $value ? '1' : '0';
            $affectedRows = self::update($setting);
        }
        self::invalidateCache($key);
        return $affectedRows;
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
        $cache = new \OmegaUp\Cache(\OmegaUp\Cache::SYSTEM_SETTINGS, $key);
        $cached = $cache->get();
        if (!is_null($cached)) {
            return strval($cached);
        }
        $setting = self::getByKey($key);
        if (is_null($setting)) {
            return $default;
        }
        $value = strval($setting->setting_value);
        $cache->set($value, timeout: 0);
        return $value;
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
            $newSetting = new \OmegaUp\DAO\VO\SystemSettings([
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_description' => '',
            ]);
            $affectedRows = self::create($newSetting);
        } else {
            $setting->setting_value = $value;
            $affectedRows = self::update($setting);
        }
        self::invalidateCache($key);
        return $affectedRows;
    }
}
