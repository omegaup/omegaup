<?php

namespace OmegaUp;

class CreateUserParams {
    /**
     * @readonly
     * @var string
     */
    public $username;

    /**
     * @readonly
     * @var null|string
     */
    public $name;

    /**
     * @readonly
     * @var null|string
     */
    public $email;

    /**
     * @readonly
     * @var null|string
     */
    public $password;

    /**
     * @readonly
     * @var string
     */
    public $scholarDegree;

    /**
     * @readonly
     * @var bool
     */
    public $isPrivate = false;

    /**
     * @readonly
     * @var null|string
     */
    public $gender = null;

    /**
     * @var null|string
     */
    public $facebookUserId = null;

    /**
     * @readonly
     * @var null|string
     */
    public $recaptcha = null;

    /**
     * @readonly
     * @var int|null
     */
    public $birthDate = null;

     /**
     * @readonly
     * @var null|string
     */
    public $parentEmail = null;

    /**
     * @param array{birth_date?: int, email?: null|string, gender?: string, is_private?: string, name?: string, parent_email?: null|string, password?: string, recaptcha?: string, scholar_degree?: string, username?: string} $params
     */
    public function __construct($params = []) {
        \OmegaUp\Validators::validateValidUsername(
            $params['username'] ?? null,
            'username'
        );
        $this->username = $params['username'] ?? '';

        $this->name = $params['name'] ?? null;

        $this->email = null;
        if (isset($params['email'])) {
            \OmegaUp\Validators::validateEmail($params['email'], 'email');
            if (isset($params['parent_email'])) {
                // It's not valid to provide both email and parent_email.
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'parent_email'
                );
            }
            $this->email = $params['email'];
        } elseif (isset($params['parent_email'])) {
            $this->parentEmail = $params['parent_email'];
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'email'
            );
        }
        $this->password = $params['password'] ?? null;

        \OmegaUp\Validators::validateInEnum(
            $params['scholar_degree'] ?? 'none',
            'scholar_degree',
            \OmegaUp\Controllers\User::ALLOWED_SCHOLAR_DEGREES
        );
        $this->scholarDegree = $params['scholar_degree'] ?? 'none';

        if (isset($params['is_private'])) {
            $this->isPrivate = boolval($params['is_private']);
        }

        $this->gender = $params['gender'] ?? null;

        $this->recaptcha = $params['recaptcha'] ?? null;

         // TODO: Assert that the birth date is always passed, and if and only if the user is U13,
        // the parent email is passed.
        $this->birthDate = $params['birth_date'] ?? null;
    }
}
