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
     * @var string
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
     * @param array{username?: string, name?: string, email?: string, password?: string, scholar_degree?: string, is_private?: string, gender?: string, recaptcha?: string, birth_date?: int, parent_email?: string} $params
     */

    public function __construct($params = []) {
        \OmegaUp\Validators::validateValidUsername(
            $params['username'] ?? null,
            'username'
        );
        $this->username = $params['username'] ?? '';

        $this->name = $params['name'] ?? null;

        \OmegaUp\Validators::validateEmail($params['email'] ?? null, 'email');
        $this->email = $params['email'] ?? '';

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

        if (isset($params['birth_date'])) {
            $this->birthDate = intval($params['birth_date']);
        }

        if (isset($params['parent_email'])) {
            $this->parentEmail = $params['parent_email'];
        }
    }
}
