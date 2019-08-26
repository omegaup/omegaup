<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Users.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Users extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'user_id' => true,
        'username' => true,
        'facebook_user_id' => true,
        'password' => true,
        'git_token' => true,
        'main_email_id' => true,
        'main_identity_id' => true,
        'scholar_degree' => true,
        'graduation_date' => true,
        'birth_date' => true,
        'verified' => true,
        'verification_id' => true,
        'reset_digest' => true,
        'reset_sent_at' => true,
        'hide_problem_tags' => true,
        'in_mailing_list' => true,
        'is_private' => true,
        'preferred_language' => true,
    ];

    /**
     * Constructor de Users
     *
     * Para construir un objeto de tipo Users debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new Exception('Unknown columns: ' . join(', ', array_keys($unknownColumns)));
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['username'])) {
            $this->username = strval($data['username']);
        }
        if (isset($data['facebook_user_id'])) {
            $this->facebook_user_id = strval($data['facebook_user_id']);
        }
        if (isset($data['password'])) {
            $this->password = strval($data['password']);
        }
        if (isset($data['git_token'])) {
            $this->git_token = strval($data['git_token']);
        }
        if (isset($data['main_email_id'])) {
            $this->main_email_id = (int)$data['main_email_id'];
        }
        if (isset($data['main_identity_id'])) {
            $this->main_identity_id = (int)$data['main_identity_id'];
        }
        if (isset($data['scholar_degree'])) {
            $this->scholar_degree = strval($data['scholar_degree']);
        }
        if (isset($data['graduation_date'])) {
            $this->graduation_date = strval($data['graduation_date']);
        }
        if (isset($data['birth_date'])) {
            $this->birth_date = strval($data['birth_date']);
        }
        if (isset($data['verified'])) {
            $this->verified = boolval($data['verified']);
        }
        if (isset($data['verification_id'])) {
            $this->verification_id = strval($data['verification_id']);
        }
        if (isset($data['reset_digest'])) {
            $this->reset_digest = strval($data['reset_digest']);
        }
        if (isset($data['reset_sent_at'])) {
            /**
             * @var string|int|float $data['reset_sent_at']
             * @var int $this->reset_sent_at
             */
            $this->reset_sent_at = \OmegaUp\DAO\DAO::fromMySQLTimestamp($data['reset_sent_at']);
        }
        if (isset($data['hide_problem_tags'])) {
            $this->hide_problem_tags = boolval($data['hide_problem_tags']);
        }
        if (isset($data['in_mailing_list'])) {
            $this->in_mailing_list = boolval($data['in_mailing_list']);
        }
        if (isset($data['is_private'])) {
            $this->is_private = boolval($data['is_private']);
        }
        if (isset($data['preferred_language'])) {
            $this->preferred_language = strval($data['preferred_language']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $user_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $username = null;

    /**
     * Facebook ID for this user.
     *
     * @var string|null
     */
    public $facebook_user_id = null;

    /**
     * Contraseña del usuario, usando Argon2i o Blowfish
     *
     * @var string|null
     */
    public $password = null;

    /**
     * Token de acceso para git, usando Argon2i
     *
     * @var string|null
     */
    public $git_token = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $main_email_id = null;

    /**
     * Identidad principal del usuario
     *
     * @var int|null
     */
    public $main_identity_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $scholar_degree = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $graduation_date = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $birth_date = null;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $verified = false;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $verification_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $reset_digest = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $reset_sent_at = null;

    /**
     * Determina si el usuario quiere ocultar las etiquetas de los problemas
     *
     * @var bool|null
     */
    public $hide_problem_tags = null;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $in_mailing_list = false;

    /**
     * Determina si el usuario eligió no compartir su información de manera pública
     *
     * @var bool
     */
    public $is_private = false;

    /**
     * El lenguaje de programación de preferencia de este usuario
     *
     * @var string|null
     */
    public $preferred_language = null;
}
