<?php

namespace OmegaUp\Controllers;

/**
 * Controllers parent class
 */
class Controller {
    /** @var \Monolog\Logger */
    public static $log;

    /**
     * Calls authenticateRequest and throws only if authentication fails AND
     * there's no target username in Request.
     * This is to allow unauthenticated access to APIs that work for both
     * current authenticated user and a targeted user (via $r["username"])
     *
     * @omegaup-request-param mixed $username
     *
     * @param \OmegaUp\Request $r
     */
    protected static function authenticateOrAllowUnauthenticatedRequest(
        \OmegaUp\Request $r
    ): void {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // allow unauthenticated only if it has $r["username"]
            if (is_null($r['username'])) {
                throw $e;
            }
        }
    }

    /**
     * Resolves the target user for the API. If a username is provided in
     * the request, then we use that one. Otherwise, we use currently logged-in
     * user.
     *
     * Request must be authenticated before this function is called.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @omegaup-request-param string $username
     */
    protected static function resolveTargetUser(
        \OmegaUp\Request $r
    ): ?\OmegaUp\DAO\VO\Users {
        // By default use current user
        $user = $r->user;

        if (!is_null($r['username'])) {
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['username'],
                'username'
            );
            $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
            if (is_null($user)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
        }

        return $user;
    }

    /**
     * Resolves the target identity for the API. If a username is provided in
     * the request, then we use that one. Otherwise, we use currently logged-in
     * identity.
     *
     * Request must be authenticated before this function is called.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @omegaup-request-param null|string $username
     */
    protected static function resolveTargetIdentity(
        \OmegaUp\Request $r
    ): ?\OmegaUp\DAO\VO\Identities {
        // By default use current identity
        $identity = $r->identity;

        $username = $r->ensureOptionalString(
            'username',
            required: false,
            validator: fn (string $name) => \OmegaUp\Validators::normalUsername(
                $name
            )
        );

        if (!is_null($username)) {
            $identity = \OmegaUp\DAO\Identities::findByUsername($username);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
        }

        return $identity;
    }

    /**
     * Gets the current month's first day date.
     */
    protected static function getCurrentMonthFirstDay(?string $date): string {
        if (empty($date)) {
            // Get first day of the current month
            return date('Y-m-01', \OmegaUp\Time::get());
        }
        $date = strtotime($date);
        if ($date === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'date'
            );
        }
        return date('Y-m-01', $date);
    }

    /**
     * Update properties of $object based on what is provided in $request.
     * $properties can have 'simple' and 'complex' properties.
     * - A simple property is just a name using underscores, and it's getter and setter methods should
     *   be the camel case version of the property name.
     * - An advanced property can have:
     *   > A getter/setter base name
     *   > A flag indicating it is important. Important properties are checked to determined if they
     *     really changed. For example: properties that should cause a problem to be rejudged,
     *     like time limits or memory constraints.
     *   > A transform method that takes the new property value stored in the request and transforms
     *     it into the proper form that should be stored in $object. For example:
     *     function($value) { return gmdate('Y-m-d H:i:s', $value); }
     *
     * @psalm-suppress RequestAccessNotALiteralString  This is the only function that's allowed to access parameters as a non-string literal.
     * @param \OmegaUp\Request $request
     * @param object $object
     * @param array<int|string, string|array{transform?: callable(mixed):mixed, important?: bool}> $properties
     * @return bool True if there were changes to any property marked as 'important'.
     */
    protected static function updateValueProperties(
        \OmegaUp\Request $request,
        object $object,
        array $properties
    ): bool {
        $importantChange = false;

        foreach ($properties as $source => $info) {
            $propertyConfig = self::parsePropertyConfiguration($source, $info);

            if (is_null($request[$propertyConfig['fieldName']])) {
                continue;
            }

            $value = self::getTransformedValue(
                $request[$propertyConfig['fieldName']],
                $propertyConfig['transform']
            );

            if (
                self::isImportantChange(
                    $propertyConfig['important'],
                    $importantChange,
                    $value,
                    $object,
                    $propertyConfig['fieldName']
                )
            ) {
                $importantChange = true;
            }

            $object->{$propertyConfig['fieldName']} = $value;
        }

        return $importantChange;
    }

    /**
     * Parse property configuration from properties array
     *
     * @param int|string $source
     * @param string|array{transform?: callable(mixed):mixed, important?: bool} $info
     * @return array{fieldName: string, transform: null|callable(mixed):mixed, important: bool}
     */
    private static function parsePropertyConfiguration($source, $info): array {
        /** @var null|callable(mixed):mixed */
        $transform = null;
        $important = false;
        /** @var string */
        $fieldName = '';

        if (is_int($source)) {
            assert(is_string($info));
            $fieldName = $info;
        } else {
            $fieldName = $source;
            if (is_array($info)) {
                if (isset($info['transform'])) {
                    $transform = $info['transform'];
                }
                if (isset($info['important']) && $info['important'] === true) {
                    $important = $info['important'];
                }
            }
        }

        return [
            'fieldName' => $fieldName,
            'transform' => $transform,
            'important' => $important,
        ];
    }

    /**
     * Get transformed value using transform function if provided
     *
     * @param mixed $value
     * @param null|callable(mixed):mixed $transform
     * @return mixed
     */
    private static function getTransformedValue($value, ?callable $transform) {
        if (is_null($transform)) {
            return $value;
        }
        return $transform($value);
    }

    /**
     * Check if this is an important property change
     *
     * @param bool $important
     * @param bool $importantChange
     * @param mixed $value
     * @param object $object
     * @param string $fieldName
     * @return bool
     */
    private static function isImportantChange(
        bool $important,
        bool $importantChange,
        $value,
        object $object,
        string $fieldName
    ): bool {
        if (!$important || $importantChange) {
            return false;
        }
        return $value != $object->$fieldName;
    }

    public static function ensureNotInLockdown(): void {
        /** @psalm-suppress TypeDoesNotContainType this can be defined to true sometimes. */
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }
    }
}

\OmegaUp\Controllers\Controller::$log = \Monolog\Registry::omegaup()->withName(
    'controller'
);
