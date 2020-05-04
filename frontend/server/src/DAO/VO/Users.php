<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Users`.
 *
 * @access public
 */
class Users extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'user_id' => true,
        'facebook_user_id' => true,
        'git_token' => true,
        'main_email_id' => true,
        'main_identity_id' => true,
        'scholar_degree' => true,
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

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['facebook_user_id'])) {
            $this->facebook_user_id = strval(
                $data['facebook_user_id']
            );
        }
        if (isset($data['git_token'])) {
            $this->git_token = strval(
                $data['git_token']
            );
        }
        if (isset($data['main_email_id'])) {
            $this->main_email_id = intval(
                $data['main_email_id']
            );
        }
        if (isset($data['main_identity_id'])) {
            $this->main_identity_id = intval(
                $data['main_identity_id']
            );
        }
        if (isset($data['scholar_degree'])) {
            $this->scholar_degree = strval(
                $data['scholar_degree']
            );
        }
        if (isset($data['birth_date'])) {
            $this->birth_date = strval(
                $data['birth_date']
            );
        }
        if (isset($data['verified'])) {
            $this->verified = boolval(
                $data['verified']
            );
        }
        if (isset($data['verification_id'])) {
            $this->verification_id = strval(
                $data['verification_id']
            );
        }
        if (isset($data['reset_digest'])) {
            $this->reset_digest = strval(
                $data['reset_digest']
            );
        }
        if (isset($data['reset_sent_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['reset_sent_at']
             * @var \OmegaUp\Timestamp $this->reset_sent_at
             */
            $this->reset_sent_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['reset_sent_at']
                )
            );
        }
        if (isset($data['hide_problem_tags'])) {
            $this->hide_problem_tags = boolval(
                $data['hide_problem_tags']
            );
        }
        if (isset($data['in_mailing_list'])) {
            $this->in_mailing_list = boolval(
                $data['in_mailing_list']
            );
        }
        if (isset($data['is_private'])) {
            $this->is_private = boolval(
                $data['is_private']
            );
        }
        if (isset($data['preferred_language'])) {
            $this->preferred_language = strval(
                $data['preferred_language']
            );
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
     * Facebook ID for this user.
     *
     * @var string|null
     */
    public $facebook_user_id = null;

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
     * @var \OmegaUp\Timestamp|null
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
