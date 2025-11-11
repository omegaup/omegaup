<?php

namespace OmegaUp;

/**
 * Conjunto de validadores genéricos
 */
class Validators {
    // The maximum length for aliases.
    const ALIAS_MAX_LENGTH = 32;
    const ZIP_MAX_FILE_SIZE_BYTES = 50 * 1024 * 1024; // 50 MB
    const ZIP_CASE_SIZE_LIMIT_BYTES = 8 * 1024; // 8 KB
    const ZIP_ALLOWED_CASE_EXTENSIONS = ['in', 'out'];
    const ZIP_FORBIDDEN_PATH_CHARS = ['..'];
    /**
     * Check if email is valid
     */
    public static function email(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check if email is valid
     *
     * @param mixed $parameter
     * @param string $parameterName Name of parameter that will appear en error message
     * @psalm-assert non-empty-string $parameter
     */
    public static function validateEmail(
        $parameter,
        string $parameterName
    ): void {
        if (!self::isPresent($parameter, $parameterName, required: true)) {
            return;
        }
        if (!filter_var($parameter, FILTER_VALIDATE_EMAIL)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                $parameterName
            );
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
    ): void {
        if (!self::isPresent($parameter, $parameterName, required: true)) {
            return;
        }

        // Validate data is string
        if (!is_string($parameter) || empty($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                $parameterName
            );
        }
    }

    /**
     * Check whether parameter value is non-empty string
     */
    public static function stringNonEmpty(string $parameter): bool {
        return !empty($parameter);
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
    ): void {
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
    ): void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        if (!is_string($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                $parameterName
            );
        }

        self::validateLengthInRange(
            $parameter,
            $parameterName,
            $minLength,
            $maxLength
        );
    }

    public static function validateLengthInRange(
        string $parameter,
        string $parameterName,
        ?int $minLength,
        ?int $maxLength
    ): void {
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
     * Checks whether the string parameter is of a certain length.
     *
     * @param string   $parameter the parameter
     * @param int|null $minLength the (optional) minimum length
     * @param int|null $maxLength the (optional) maximum length
     *
     * @return true when the parameter's length is within the specified bounds.
     */
    public static function stringOfLengthInRange(
        string $parameter,
        ?int $minLength,
        ?int $maxLength
    ): bool {
        if (!is_null($minLength) && strlen($parameter) < $minLength) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterStringTooShort',
                parameter: null,
                additionalParameters: ['min_length' => strval($minLength)],
            );
        }
        if (!is_null($maxLength) && strlen($parameter) > $maxLength) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterStringTooLong',
                parameter: null,
                additionalParameters: ['max_length' => strval($maxLength)],
            );
        }
        return true;
    }

    /**
     * Returns whether the alias is valid.
     * The form of namespaced alias is: "namespace:alias"
     *
     * @param string $alias
     * @return boolean
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function namespacedAlias(string $alias): bool {
        return (
            preg_match('/^(?:[a-zA-Z0-9_-]+:)?[a-zA-Z0-9_-]+$/', $alias) === 1
            && !self::isRestrictedAlias($alias)
            && strlen($alias) <= Validators::ALIAS_MAX_LENGTH
        );
    }

    /**
     * @param string $objectId
     * @return boolean
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function objectId(string $objectId): bool {
        return preg_match('/^[0-9a-f]{40}$/', $objectId) === 1;
    }

    /**
     * @param string $filename
     * @return boolean
     */
    public static function filename(string $filename): bool {
        return preg_match(
            '/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_.-]+$/',
            $filename
        ) === 1;
    }

    /**
     * Returns whether the alias is valid.
     *
     * @return boolean
     */
    public static function alias(
        string $alias,
        int $maxLength = Validators::ALIAS_MAX_LENGTH
    ): bool {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $alias)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'alias'
            );
        }

        if (strlen($alias) > $maxLength) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterStringTooLong',
                'alias',
                ['max_length' => strval($maxLength)]
            );
        }

        if (self::isRestrictedAlias($alias)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'alias',
            );
        }

        return true;
    }

    /**
     * Returns whether the token is valid.
     *
     * @return boolean
     */
    public static function token(string $token): bool {
        return preg_match('/^[a-zA-Z0-9]{30}$/', $token) === 1;
    }

    /**
     * Returns whether the username or email is valid.
     *
     * @param string $usernameOrEmail
     * @return boolean
     */
    public static function usernameOrTeamUsernameOrEmail(string $usernameOrEmail): bool {
        return (
            self::email($usernameOrEmail)
            || self::normalUsername($usernameOrEmail)
            || self::identityUsername($usernameOrEmail)
            || self::identityTeamUsername($usernameOrEmail)
        );
    }

    /**
     * Returns whether the username or email is valid.
     *
     * @param string $usernameOrEmail
     * @return boolean
     */
    public static function usernameOrEmail(string $usernameOrEmail): bool {
        return (
            self::email($usernameOrEmail)
            || self::normalUsername($usernameOrEmail)
            || self::identityUsername($usernameOrEmail)
        );
    }

    /**
     * Returns whether the username is valid.
     *
     * @param string $username
     * @return boolean
     */
    public static function normalUsername(string $username): bool {
        return preg_match('/^[a-zA-Z0-9_.-]+$/', $username) !== 0;
    }

    /**
     * Returns whether the username of an identity is valid.
     *
     * @param string $username
     * @return boolean
     */
    public static function identityUsername(string $username): bool {
        return (
            preg_match(
                '/^[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/',
                $username
            ) !== 0
        );
    }

    /**
     * Returns whether the username of an identity team is valid.
     *
     * @param string $username
     * @return boolean
     */
    public static function identityTeamUsername(string $username): bool {
        return (
            preg_match(
                '/^teams:[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/',
                $username
            ) !== 0
        );
    }

    /**
     * Returns whether the alias is restricted.
     *
     * @param string $alias the alias.
     * @return boolean whether the alias is restricted.
     */
    public static function isRestrictedAlias(string $alias): bool {
        $restrictedAliases = ['new', 'admin', 'problem', 'list', 'mine', 'omegaup', 'collection'];
        return in_array(strtolower($alias), $restrictedAliases);
    }

    /**
     * Enforces username requirements
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @psalm-assert non-empty-string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateValidUsername(
        $parameter,
        string $parameterName
    ): void {
        self::validateStringOfLengthInRange(
            $parameter,
            $parameterName,
            2,
            null,
            required: true,
        );

        if (preg_match('/[^a-zA-Z0-9_.-]/', $parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidAlias',
                $parameterName
            );
        }
    }

    /**
     * Enforces username requirements
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @psalm-assert string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateAlias(
        $parameter,
        string $parameterName
    ): void {
        if (!is_string($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidAlias',
                $parameterName
            );
        }
        if (!self::alias($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidAlias',
                $parameterName
            );
        }
    }

    /**
     * Enforces username identity requirements
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @psalm-assert string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateValidUsernameIdentity(
        $parameter,
        string $parameterName
    ): void {
        if (!self::isPresent($parameter, $parameterName, required: true)) {
            return;
        }
        self::validateStringOfLengthInRange(
            $parameter,
            $parameterName,
            2,
            null,
            required: true
        );

        /** @psalm-suppress RedundantConditionGivenDocblockType not sure why Psalm is complaining here. */
        if (!preg_match('/^[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/', $parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidAlias',
                $parameterName
            );
        }
    }

    /**
     * Enforces username identity team requirements
     *
     * @param mixed $parameter
     * @param string $parameterName
     * @psalm-assert string $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateValidUsernameIdentityTeam(
        $parameter,
        string $parameterName
    ): void {
        if (!self::isPresent($parameter, $parameterName, required: true)) {
            return;
        }
        self::validateStringOfLengthInRange(
            $parameter,
            $parameterName,
            minLength: 2,
            maxLength: null,
            required: true
        );

        /** @psalm-suppress RedundantConditionGivenDocblockType not sure why Psalm is complaining here. */
        if (
            !preg_match(
                '/^teams:[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/',
                $parameter
            )
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidAlias',
                $parameterName
            );
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
    ): void {
        if (!self::isPresent($parameter, $parameterName, required: true)) {
            return;
        }

        // Validate that we are working with a date
        // @TODO This strtotime() allows nice strings like "next Thursday".
        if (!is_string($parameter) || strtotime($parameter) === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                $parameterName
            );
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
    ): void {
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
     * @psalm-assert numeric $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateNumberInRange(
        $parameter,
        string $parameterName,
        $lowerBound,
        $upperBound,
        bool $required = true
    ): void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        if (!is_numeric($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotANumber',
                $parameterName
            );
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
     * @param mixed     $parameter
     * @param string    $parameterName
     * @param int|null $lowerBound
     * @param int|null $upperBound
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateTimestampInRange(
        $parameter,
        string $parameterName,
        ?int $lowerBound,
        ?int $upperBound
    ): void {
        if (!self::isPresent($parameter, $parameterName, true)) {
            return;
        }
        if (is_numeric($parameter)) {
            $parameter = intval($parameter);
        } elseif ($parameter instanceof \OmegaUp\Timestamp) {
            $parameter = $parameter->time;
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotADate',
                $parameterName
            );
        }
        if (!is_null($lowerBound) && $parameter < $lowerBound) {
            $exception = new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterDateTooSmall',
                $parameterName
            );
            $exception->addCustomMessageToArray(
                'payload',
                ['lower_bound' => $lowerBound]
            );
            throw $exception;
        }
        if (!is_null($upperBound) && $parameter > $upperBound) {
            $exception = new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterDateTooLarge',
                $parameterName
            );
            $exception->addCustomMessageToArray(
                'payload',
                ['upper_bound' => $upperBound]
            );
            throw $exception;
        }
    }

    /**
     *
     * @param mixed  $parameter
     * @param string $parameterName
     * @psalm-assert numeric $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateNumber(
        $parameter,
        string $parameterName
    ): void {
        if (!self::isPresent($parameter, $parameterName, required: true)) {
            return;
        }
        if (!is_numeric($parameter)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotANumber',
                $parameterName
            );
        }
    }

    /**
     *
     * @param mixed  $parameter
     * @param string $parameterName
     * @psalm-assert numeric|null $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateOptionalNumber(
        $parameter,
        string $parameterName,
        bool $required = false
    ): void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        self::validateNumber($parameter, $parameterName);
    }

    /**
     * @template T of scalar
     * @param mixed $parameter
     * @param string $parameterName
     * @param list<T> $enum
     * @param bool $required
     * @psalm-assert T $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateInEnum(
        $parameter,
        string $parameterName,
        array $enum
    ): void {
        if (!self::isPresent($parameter, $parameterName, required: true)) {
            return;
        }
        if (!in_array($parameter, $enum)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotInExpectedSet',
                $parameterName,
                [
                    'bad_elements' => (
                        is_scalar($parameter) || is_object($parameter) ?
                        strval($parameter) :
                        ''
                    ),
                    'expected_set' => implode(', ', $enum),
                ]
            );
        }
    }

    /**
     * @template T of scalar
     * @param mixed $parameter
     * @param string $parameterName
     * @param list<T> $enum
     * @param bool $required
     * @psalm-assert null|T $parameter
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateOptionalInEnum(
        $parameter,
        string $parameterName,
        array $enum,
        bool $required = false
    ): void {
        if (!self::isPresent($parameter, $parameterName, $required)) {
            return;
        }
        self::validateInEnum($parameter, $parameterName, $enum);
    }

    /**
     * @template T of scalar
     * @param list<T> $parameter
     * @param string $parameterName
     * @param list<T> $validOptions
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateValidSubset(
        array $parameter,
        string $parameterName,
        array $validOptions
    ): void {
        $badElements = [];
        foreach ($parameter as $element) {
            if (!in_array($element, $validOptions)) {
                $badElements[] = $element;
            }
        }
        if (!empty($badElements)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotInExpectedSet',
                $parameterName,
                [
                    'bad_elements' => implode(',', $badElements),
                    'expected_set' => implode(', ', $validOptions),
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
    ): bool {
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
    ): void {
        if (!in_array($badgeAlias, $allExistingBadges)) {
            throw new \OmegaUp\Exceptions\NotFoundException('badgeNotExist');
        }
    }

    /**
     * Check that the ZIP file was uploaded correctly and return its info.
     *
     * @psalm-suppress Superglobals
     * @return array{name: string, tmp_name: string}
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateZipUploadedFile(): array {
        if (
            !isset(
                $_FILES['zipFile']
            ) || $_FILES['zipFile']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file(
                $_FILES['zipFile']['tmp_name']
            )
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidValidZipUpload',
                'zipFile'
            );
        }
        return [
            'problemName' => pathinfo(
                $_FILES['zipFile']['name'],
                PATHINFO_FILENAME
            ),
            'tmpFilePath' => $_FILES['zipFile']['tmp_name'],
        ];
    }

    /**
     * Check the size of the ZIP file
     *
     * @param string $filePath
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateZipFileSize(string $filePath): void {
        $fileSize = filesize($filePath);

        if ($fileSize === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidFile',
                'zipFile'
            );
        }

        if ($fileSize > self::ZIP_MAX_FILE_SIZE_BYTES) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidZipFileSizeTooLong',
                'zipFile',
                ['max_length' => strval(
                    self::ZIP_MAX_FILE_SIZE_BYTES / (1024 * 1024)
                )]
            );
        }

        if ($fileSize === 0) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'zipFile'
            );
        }
    }

    /**
     * Check that it is a valid ZIP file
     *
     * @param string $filePath
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateZipIntegrity(string $filePath): void {
        $zip = new \ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidZipIntegrity',
                'zipFile'
            );
        }

        $zip->close();
    }

    /**
     * Check the path of a file within the ZIP (prevents path traversal)
     *
     * @param string $filePath
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateZipFilePath(string $filePath): void {
        foreach (self::ZIP_FORBIDDEN_PATH_CHARS as $char) {
            if (strpos($filePath, $char) !== false) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalidZipFilePath',
                    'zipFile'
                );
            }
        }
    }

    /**
     * Check the file extension and case name
     *
     * @param string $filename
     * @param string $extension
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function validateZipCaseFileName(
        string $filename,
        string $extension
    ): void {
        if (!in_array($extension, self::ZIP_ALLOWED_CASE_EXTENSIONS)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidCaseExtension',
                'zipFile'
            );
        }

        // Ensure the filename contains at most one period (e.g., 'name.ext').
        $parts = explode('.', $filename);
        if (count($parts) > 2) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalidCaseName',
                'zipFile'
            );
        }
    }
}
