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
        'has_learning_objective' => true,
        'has_teaching_objective' => true,
        'has_scholar_objective' => true,
        'has_competitive_objective' => true,
        'scholar_degree' => true,
        'birth_date' => true,
        'verified' => true,
        'verification_id' => true,
        'deletion_token' => true,
        'reset_digest' => true,
        'reset_sent_at' => true,
        'hide_problem_tags' => true,
        'in_mailing_list' => true,
        'is_private' => true,
        'preferred_language' => true,
        'parent_verified' => true,
        'creation_timestamp' => true,
        'parental_verification_token' => true,
        'parent_email_verification_initial' => true,
        'parent_email_verification_deadline' => true,
        'parent_email_id' => true,
        'x_url' => true,
        'linkedin_url' => true,
        'github_url' => true,
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
            $this->facebook_user_id = is_scalar(
                $data['facebook_user_id']
            ) ? strval($data['facebook_user_id']) : '';
        }
        if (isset($data['git_token'])) {
            $this->git_token = is_scalar(
                $data['git_token']
            ) ? strval($data['git_token']) : '';
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
        if (isset($data['has_learning_objective'])) {
            $this->has_learning_objective = boolval(
                $data['has_learning_objective']
            );
        }
        if (isset($data['has_teaching_objective'])) {
            $this->has_teaching_objective = boolval(
                $data['has_teaching_objective']
            );
        }
        if (isset($data['has_scholar_objective'])) {
            $this->has_scholar_objective = boolval(
                $data['has_scholar_objective']
            );
        }
        if (isset($data['has_competitive_objective'])) {
            $this->has_competitive_objective = boolval(
                $data['has_competitive_objective']
            );
        }
        if (isset($data['scholar_degree'])) {
            $this->scholar_degree = is_scalar(
                $data['scholar_degree']
            ) ? strval($data['scholar_degree']) : '';
        }
        if (isset($data['birth_date'])) {
            $this->birth_date = is_scalar(
                $data['birth_date']
            ) ? strval($data['birth_date']) : '';
        }
        if (isset($data['verified'])) {
            $this->verified = boolval(
                $data['verified']
            );
        }
        if (isset($data['verification_id'])) {
            $this->verification_id = is_scalar(
                $data['verification_id']
            ) ? strval($data['verification_id']) : '';
        }
        if (isset($data['deletion_token'])) {
            $this->deletion_token = is_scalar(
                $data['deletion_token']
            ) ? strval($data['deletion_token']) : '';
        }
        if (isset($data['reset_digest'])) {
            $this->reset_digest = is_scalar(
                $data['reset_digest']
            ) ? strval($data['reset_digest']) : '';
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
            $this->preferred_language = is_scalar(
                $data['preferred_language']
            ) ? strval($data['preferred_language']) : '';
        }
        if (isset($data['parent_verified'])) {
            $this->parent_verified = boolval(
                $data['parent_verified']
            );
        }
        if (isset($data['creation_timestamp'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['creation_timestamp']
             * @var \OmegaUp\Timestamp $this->creation_timestamp
             */
            $this->creation_timestamp = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['creation_timestamp']
                )
            );
        } else {
            $this->creation_timestamp = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['parental_verification_token'])) {
            $this->parental_verification_token = is_scalar(
                $data['parental_verification_token']
            ) ? strval($data['parental_verification_token']) : '';
        }
        if (isset($data['parent_email_verification_initial'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['parent_email_verification_initial']
             * @var \OmegaUp\Timestamp $this->parent_email_verification_initial
             */
            $this->parent_email_verification_initial = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['parent_email_verification_initial']
                )
            );
        }
        if (isset($data['parent_email_verification_deadline'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['parent_email_verification_deadline']
             * @var \OmegaUp\Timestamp $this->parent_email_verification_deadline
             */
            $this->parent_email_verification_deadline = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['parent_email_verification_deadline']
                )
            );
        }
        if (isset($data['parent_email_id'])) {
            $this->parent_email_id = intval(
                $data['parent_email_id']
            );
        }
        if (isset($data['x_url'])) {
            $this->x_url = is_scalar(
                $data['x_url']
            ) ? strval($data['x_url']) : '';
        }
        if (isset($data['linkedin_url'])) {
            $this->linkedin_url = is_scalar(
                $data['linkedin_url']
            ) ? strval($data['linkedin_url']) : '';
        }
        if (isset($data['github_url'])) {
            $this->github_url = is_scalar(
                $data['github_url']
            ) ? strval($data['github_url']) : '';
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
     * Dice si el usuario expresó tener el objetivo de usar omegaUp para aprender.
     *
     * @var bool|null
     */
    public $has_learning_objective = null;

    /**
     * Dice si el usuario expresó tener el objetivo de usar omegaUp para enseñar.
     *
     * @var bool|null
     */
    public $has_teaching_objective = null;

    /**
     * Dice si el usuario expresó tener el objetivo de usar omegaUp para la escuela.
     *
     * @var bool|null
     */
    public $has_scholar_objective = null;

    /**
     * Dice si el usuario expresó tener el objetivo de usar omegaUp para programación competitiva.
     *
     * @var bool|null
     */
    public $has_competitive_objective = null;

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
    public $deletion_token = null;

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

    /**
     * Almacena la respuesta del padre cuando este verifica la cuenta de su hijo
     *
     * @var bool|null
     */
    public $parent_verified = null;

    /**
     * Almacena la hora y fecha en que se creó la cuenta de usuario
     *
     * @var \OmegaUp\Timestamp
     */
    public $creation_timestamp;  // CURRENT_TIMESTAMP

    /**
     * Token que se generará para los usuarios menores de 13 años al momento de registrar su cuenta, el cuál será enviado por correo electrónico al padre
     *
     * @var string|null
     */
    public $parental_verification_token = null;

    /**
     * Almacena la hora en que se envió el correo electrónico de verificación
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $parent_email_verification_initial = null;

    /**
     * Almacena la hora y fecha límite que tienen los padres para verificar la cuenta de su hijo menor a 13 años
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $parent_email_verification_deadline = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $parent_email_id = null;

    /**
     * URL del perfil en X (antes Twitter)
     *
     * @var string|null
     */
    public $x_url = null;

    /**
     * URL de perfil en LinkedIn
     *
     * @var string|null
     */
    public $linkedin_url = null;

    /**
     * URL de perfil en GitHub
     *
     * @var string|null
     */
    public $github_url = null;
}
