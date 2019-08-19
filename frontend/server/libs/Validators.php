<?php

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
     * @param bool $required If $required is TRUE and the parameter is not present, check fails.
     * @throws InvalidArgumentException
     */
    public static function validateEmail(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        if (!filter_var($parameter, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidParameterException('parameterInvalid', $parameterName);
        }
    }

    /**
     * Check if string is string and not empty
     *
     * @param mixed $parameter
     * @param string $parameterName Name of parameter that will appear en error message
     * @param bool $required If $required is TRUE and the parameter is not present, check fails.
     * @throws InvalidArgumentException
     */
    public static function validateStringNonEmpty(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }

        // Validate data is string
        if (!is_string($parameter) || empty($parameter)) {
            throw new InvalidParameterException('parameterEmpty', $parameterName);
        }
    }

    /**
     * @param mixed  $parameter
     * @param string $parameterName
     * @param ?int   $minLength
     * @param ?int   $maxLength
     * @param bool   $required
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
            throw new InvalidParameterException('parameterInvalid', $parameterName);
        }

        if (!is_null($minLength) && strlen($parameter) < $minLength) {
            throw new InvalidParameterException(
                'parameterStringTooShort',
                $parameterName,
                ['min_length' => $minLength]
            );
        }
        if (!is_null($maxLength) && strlen($parameter) > $maxLength) {
            throw new InvalidParameterException(
                'parameterStringTooLong',
                $parameterName,
                ['max_length' => $maxLength]
            );
        }
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param bool $required
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
            throw new InvalidParameterException('parameterInvalidAlias', $parameterName);
        }
        if (self::isRestrictedAlias($parameter)) {
            throw new DuplicatedEntryInDatabaseException('aliasInUse');
        }
        if (!self::isValidAlias($parameter)) {
            throw new InvalidParameterException('parameterInvalidAlias', $parameterName);
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
     * @param string $parameter
     * @param string $parameterName
     * @param bool $required
     * @throws InvalidParameterException
     */
    public static function validateValidUsername(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        self::validateStringOfLengthInRange($parameter, $parameterName, 2, null, $required);

        if (preg_match('/[^a-zA-Z0-9_.-]/', $parameter)) {
            throw new InvalidParameterException('parameterInvalidAlias', $parameterName);
        }
    }

    /**
     * Enforces username identity requirements
     *
     * @param string $parameter
     * @param string $parameterName
     * @param bool $required
     * @throws InvalidParameterException
     */
    public static function validateValidUsernameIdentity(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        self::validateStringOfLengthInRange($parameter, $parameterName, 2, null, $required);

        if (!preg_match('/^[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/', $parameter)) {
            throw new InvalidParameterException('parameterInvalidAlias', $parameterName);
        }
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param bool $required
     * @throws InvalidParameterException
     */
    public static function validateDate(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }

        // Validate that we are working with a date
        // @TODO This strtotime() allows nice strings like "next Thursday".
        if (!is_string($parameter) || strtotime($parameter) === false) {
            throw new InvalidParameterException('parameterInvalid', $parameterName);
        }
    }

    /**
     *
     * @param mixed     $parameter
     * @param string    $parameterName
     * @param int|float|null $lowerBound
     * @param int|float|null $upperBound
     * @param boolean   $required
     * @throws InvalidParameterException
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
            throw new InvalidParameterException('parameterNotANumber', $parameterName);
        }
        // Coerce $parameter into a numeric value.
        $parameter = $parameter + 0;
        if (!is_null($lowerBound) && $parameter < $lowerBound) {
            throw new InvalidParameterException(
                'parameterNumberTooSmall',
                $parameterName,
                ['lower_bound' => $lowerBound]
            );
        }
        if (!is_null($upperBound) && $parameter > $upperBound) {
            throw new InvalidParameterException(
                'parameterNumberTooLarge',
                $parameterName,
                ['upper_bound' => $upperBound]
            );
        }
    }

    /**
     *
     * @param mixed  $parameter
     * @param string $parameterName
     * @param bool   $required
     * @throws InvalidParameterException
     */
    public static function validateNumber(
        $parameter,
        string $parameterName,
        bool $required = true
    ) : void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        if (!is_numeric($parameter)) {
            throw new InvalidParameterException('parameterNotANumber', $parameterName);
        }
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param array $enum
     * @param bool $required
     * @throws InvalidParameterException
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
            throw new InvalidParameterException(
                'parameterNotInExpectedSet',
                $parameterName,
                ['bad_elements' => $parameter, 'expected_set' => implode(', ', $enum)]
            );
        }
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param array $enum
     * @param bool $required
     * @throws InvalidParameterException
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
            throw new InvalidParameterException('parameterInvalid', $parameterName);
        }

        $badElements = [];
        $elements = array_filter(explode(',', $parameter));
        foreach ($elements as $element) {
            if (!in_array($element, $enum)) {
                $badElements[] = $element;
            }
        }
        if (!empty($badElements)) {
            throw new InvalidParameterException(
                'parameterNotInExpectedSet',
                $parameterName,
                ['bad_elements' => implode(',', $badElements), 'expected_set' => implode(', ', $enum)]
            );
        }
    }

    /**
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @param bool $required
     * @throws InvalidParameterException
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
            throw new InvalidParameterException('parameterEmpty', $parameterName);
        }
        return false;
    }

    /**
     * Checks if badge exists in the allExistingBadges array,
     * if not, it throws an exception.
     *
     * @param string $badgeAlias
     * @param array $allExistingBadges
     * @throws NotFoundException
     */
    public static function validateBadgeExists(
        string $badgeAlias,
        array $allExistingBadges
    ) : void {
        if (!in_array($badgeAlias, $allExistingBadges)) {
            throw new NotFoundException('badgeNotExist');
        }
    }
}
