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
 * Value Object class for table `Problemsets`.
 *
 * @access public
 */
class Problemsets extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'problemset_id' => true,
        'acl_id' => true,
        'access_mode' => true,
        'languages' => true,
        'needs_basic_information' => true,
        'requests_user_information' => true,
        'scoreboard_url' => true,
        'scoreboard_url_admin' => true,
        'type' => true,
        'contest_id' => true,
        'assignment_id' => true,
        'interview_id' => true,
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
        if (isset($data['problemset_id'])) {
            $this->problemset_id = intval(
                $data['problemset_id']
            );
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = intval(
                $data['acl_id']
            );
        }
        if (isset($data['access_mode'])) {
            $this->access_mode = is_scalar(
                $data['access_mode']
            ) ? strval($data['access_mode']) : '';
        }
        if (isset($data['languages'])) {
            $this->languages = is_scalar(
                $data['languages']
            ) ? strval($data['languages']) : '';
        }
        if (isset($data['needs_basic_information'])) {
            $this->needs_basic_information = boolval(
                $data['needs_basic_information']
            );
        }
        if (isset($data['requests_user_information'])) {
            $this->requests_user_information = is_scalar(
                $data['requests_user_information']
            ) ? strval($data['requests_user_information']) : '';
        }
        if (isset($data['scoreboard_url'])) {
            $this->scoreboard_url = is_scalar(
                $data['scoreboard_url']
            ) ? strval($data['scoreboard_url']) : '';
        }
        if (isset($data['scoreboard_url_admin'])) {
            $this->scoreboard_url_admin = is_scalar(
                $data['scoreboard_url_admin']
            ) ? strval($data['scoreboard_url_admin']) : '';
        }
        if (isset($data['type'])) {
            $this->type = is_scalar(
                $data['type']
            ) ? strval($data['type']) : '';
        }
        if (isset($data['contest_id'])) {
            $this->contest_id = intval(
                $data['contest_id']
            );
        }
        if (isset($data['assignment_id'])) {
            $this->assignment_id = intval(
                $data['assignment_id']
            );
        }
        if (isset($data['interview_id'])) {
            $this->interview_id = intval(
                $data['interview_id']
            );
        }
    }

    /**
     * El identificador único para cada conjunto de problemas
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $problemset_id = 0;

    /**
     * La lista de control de acceso compartida con su container
     *
     * @var int|null
     */
    public $acl_id = null;

    /**
     * La modalidad de acceso a este conjunto de problemas
     *
     * @var string
     */
    public $access_mode = 'public';

    /**
     * Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas
     *
     * @var string|null
     */
    public $languages = null;

    /**
     * Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un concurso sólo si ya llenó su información de perfil
     *
     * @var bool
     */
    public $needs_basic_information = false;

    /**
     * Se solicita información de los participantes para contactarlos posteriormente.
     *
     * @var string
     */
    public $requests_user_information = 'no';

    /**
     * Token para la url del scoreboard en problemsets
     *
     * @var string|null
     */
    public $scoreboard_url = null;

    /**
     * Token para la url del scoreboard de admin en problemsets
     *
     * @var string|null
     */
    public $scoreboard_url_admin = null;

    /**
     * Almacena el tipo de problemset que se ha creado
     *
     * @var string
     */
    public $type = 'Contest';

    /**
     * Id del concurso
     *
     * @var int|null
     */
    public $contest_id = null;

    /**
     * Id del curso
     *
     * @var int|null
     */
    public $assignment_id = null;

    /**
     * Id de la entrevista
     *
     * @var int|null
     */
    public $interview_id = null;
}
