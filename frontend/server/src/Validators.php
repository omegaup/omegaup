<?php

namespace OmegaUp;

/**
 * Conjunto de validadores genéricos
 *
 * @author joemmanuel
 */
class Validators {
    /**
     * Check if email is valid
     *
     * @param mixed $parameter
     * @param string $parameterName Name of parameter that will appear en error message
     * @psalm-assert string $parameter
     */
    public static function validateEmail(
        $parameter,
        string $parameterName
    ) : void {
        if (!self::isPresent($parameter, $parameterName, /*required=*/true)) {
            return;
        }
        if (!filter_var($parameter, FILTER_VALIDATE_EMAIL)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', $parameterName);
        }
    }

    /**
     * Check if string is string and not empty
     *
     * @param mixed $parameter
     * @param string $parameterName Name of parameter that will appear en error message
     * @psalm-assert string $parameter
     */
    public static function validateStringNonEmpty(
        $parameter,
        string $parameterName
    ) : void {
        if (!self::isPresent($parameter, $parameterName, /*required=*/true)) {
            return;
        }

        // Validate data is string
        if (!is_string($parameter) || empty($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', $parameterName);
        }
    }

    /**
     * Check if a parameter is present, it is a non-empty string.
     *
     * @param mixed $parameter
     * @param string $parameterName Name of parameter that will appear en error message
     * @param bool $required If $required is TRUE and the parameter is not present, check fails.
     * @psalm-assert null|string $parameter
     */
    public static function validateOptionalStringNonEmpty(
        $parameter,
        string $parameterName,
        bool $required = false
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        self::validateStringNonEmpty($parameter, $parameterName);
    }

    /**
     * @param mixed  $parameter
     * @param string $parameterName
     * @param ?int   $minLength
     * @param ?int   $maxLength
     * @param bool   $required
     * @psalm-assert string $parameter
     */
    public static function validateStringOfLengthInRange(
        $parameter,
        string $parameterName,
        ?int $minLength,
        ?int $maxLength,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        if (!is_string($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', $parameterName);
        }

        if (!is_null($minLength) && strlen($parameter) < $minLength) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterStringTooShort',
                $parameterName,
                ['min_length' => strval($minLength)]
            );
        }
        if (!is_null($maxLength) && strlen($parameter) > $maxLength) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterStringTooLong',
                $parameterName,
                ['max_length' => strval($maxLength)]
            );
        }
    }

    /**
     * @param mixed $parameter
     * @param string $parameterName
     * @param bool $required
     * @psalm-assert string $parameter
     */
    public static function validateValidAlias(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }

        if (!is_string($parameter) ||
            empty($parameter) ||
            strlen($parameter) > 32
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalidAlias', $parameterName);
        }
        if (self::isRestrictedAlias($parameter)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('aliasInUse');
        }
        if (!self::isValidAlias($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalidAlias', $parameterName);
        }
    }

    /**
     * Returns whether the alias is valid and is not a restricted alias.
     *
     * @param string $alias
     * @return boolean
     */
    public static function isValidAlias(string $alias) : bool {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $alias) === 1 && !self::isRestrictedAlias($alias);
    }

    /**
     * Returns whether the alias is restricted.
     *
     * @param string $alias the alias.
     * @return boolean whether the alias is restricted.
     */
    public static function isRestrictedAlias(string $alias) : bool {
        $restrictedAliases = ['new', 'admin', 'problem', 'list', 'mine', 'omegaup'];
        return in_array(strtolower($alias), $restrictedAliases);
    }

    /**
     * Enforces username requirements
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @psalm-assert string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateValidUsername(
        $parameter,
        string $parameterName
    ) : void {
        self::validateStringOfLengthInRange($parameter, $parameterName, 2, null, /*required=*/true);

        if (preg_match('/[^a-zA-Z0-9_.-]/', $parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalidAlias', $parameterName);
        }
    }

    /**
     * Enforces username identity requirements
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param bool $required
     * @psalm-assert string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateValidUsernameIdentity(
        $parameter,
        string $parameterName
    ) : void {
        if (!self::isPresent($parameter, $parameterName, /*required=*/true)) {
            return;
        }
        self::validateStringOfLengthInRange($parameter, $parameterName, 2, null, /*required=*/true);

        if (!preg_match('/^[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/', $parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalidAlias', $parameterName);
        }
    }

    /**
     * @param mixed $parameter
     * @param string $parameterName
     * @psalm-assert string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateDate(
        $parameter,
        string $parameterName
    ) : void {
        if (!self::isPresent($parameter, $parameterName, /*required=*/true)) {
            return;
        }

        // Validate that we are working with a date
        // @TODO This strtotime() allows nice strings like "next Thursday".
        if (!is_string($parameter) || strtotime($parameter) === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', $parameterName);
        }
    }

    /**
     * @param mixed $parameter
     * @param string $parameterName
     * @param bool $required
     * @psalm-assert null|string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateOptionalDate(
        $parameter,
        string $parameterName,
        bool $required = false
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        self::validateDate($parameter, $parameterName);
    }

    /**
     *
     * @param mixed     $parameter
     * @param string    $parameterName
     * @param int|float|null $lowerBound
     * @param int|float|null $upperBound
     * @param boolean   $required
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateNumberInRange(
        $parameter,
        string $parameterName,
        $lowerBound,
        $upperBound,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        if (!is_numeric($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotANumber', $parameterName);
        }
        // Coerce $parameter into a numeric value.
        $parameter = $parameter + 0;
        if (!is_null($lowerBound) && $parameter < $lowerBound) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNumberTooSmall',
                $parameterName,
                ['lower_bound' => strval($lowerBound)]
            );
        }
        if (!is_null($upperBound) && $parameter > $upperBound) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNumberTooLarge',
                $parameterName,
                ['upper_bound' => strval($upperBound)]
            );
        }
    }

    /**
     *
     * @param mixed  $parameter
     * @param string $parameterName
     * @psalm-assert int $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateNumber(
        $parameter,
        string $parameterName
    ) : void {
        if (!self::isPresent($parameter, $parameterName, /*required=*/true)) {
            return;
        }
        if (!is_numeric($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotANumber', $parameterName);
        }
    }

    /**
     *
     * @param mixed  $parameter
     * @param string $parameterName
     * @psalm-assert int $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateOptionalNumber(
        $parameter,
        string $parameterName,
        bool $required = false
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        self::validateNumber($parameter, $parameterName);
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param array $enum
     * @param bool $required
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateInEnum(
        $parameter,
        string $parameterName,
        array $enum,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }

        if (!in_array($parameter, $enum)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotInExpectedSet',
                $parameterName,
                [
                    'bad_elements' => strval($parameter),
                    'expected_set' => implode(', ', $enum),
                ]
            );
        }
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param array $enum
     * @param bool $required
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateValidSubset(
        $parameter,
        string $parameterName,
        array $enum,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        if (!is_string($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', $parameterName);
        }

        $badElements = [];
        $elements = array_filter(explode(',', $parameter));
        foreach ($elements as $element) {
            if (!in_array($element, $enum)) {
                $badElements[] = $element;
            }
        }
        if (!empty($badElements)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotInExpectedSet',
                $parameterName,
                [
                    'bad_elements' => implode(',', $badElements),
                    'expected_set' => implode(', ', $enum),
                ]
            );
        }
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param bool $required
     * @psalm-assert-if-true !null $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function isPresent(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : bool {
        if (!is_null($parameter)) {
            return true;
        }
        if ($required) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $parameterName
            );
        }
        return false;
    }

    /**
     * Checks if badge exists in the allExistingBadges array,
     * if not, it throws an exception.
     *
     * @param string $badgeAlias
     * @param array $allExistingBadges
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function validateBadgeExists(
        string $badgeAlias,
        array $allExistingBadges
    ) : void {
        if (!in_array($badgeAlias, $allExistingBadges)) {
            throw new \OmegaUp\Exceptions\NotFoundException('badgeNotExist');
        }
    }
}
