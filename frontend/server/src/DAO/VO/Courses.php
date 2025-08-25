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
 * Value Object class for table `Courses`.
 *
 * @access public
 */
class Courses extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'course_id' => true,
        'name' => true,
        'description' => true,
        'objective' => true,
        'alias' => true,
        'group_id' => true,
        'acl_id' => true,
        'level' => true,
        'start_time' => true,
        'finish_time' => true,
        'admission_mode' => true,
        'school_id' => true,
        'needs_basic_information' => true,
        'requests_user_information' => true,
        'show_scoreboard' => true,
        'languages' => true,
        'archived' => true,
        'minimum_progress_for_certificate' => true,
        'certificates_status' => true,
        'recommended' => true,
        'teaching_assistant_enabled' => true,
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
        if (isset($data['course_id'])) {
            $this->course_id = intval(
                $data['course_id']
            );
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['description'])) {
            $this->description = is_scalar(
                $data['description']
            ) ? strval($data['description']) : '';
        }
        if (isset($data['objective'])) {
            $this->objective = is_scalar(
                $data['objective']
            ) ? strval($data['objective']) : '';
        }
        if (isset($data['alias'])) {
            $this->alias = is_scalar(
                $data['alias']
            ) ? strval($data['alias']) : '';
        }
        if (isset($data['group_id'])) {
            $this->group_id = intval(
                $data['group_id']
            );
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = intval(
                $data['acl_id']
            );
        }
        if (isset($data['level'])) {
            $this->level = is_scalar(
                $data['level']
            ) ? strval($data['level']) : '';
        }
        if (isset($data['start_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['start_time']
             * @var \OmegaUp\Timestamp $this->start_time
             */
            $this->start_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['start_time']
                )
            );
        } else {
            $this->start_time = new \OmegaUp\Timestamp(
                946706400
            ); // 2000-01-01 06:00:00
        }
        if (isset($data['finish_time'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['finish_time']
             * @var \OmegaUp\Timestamp $this->finish_time
             */
            $this->finish_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['finish_time']
                )
            );
        }
        if (isset($data['admission_mode'])) {
            $this->admission_mode = is_scalar(
                $data['admission_mode']
            ) ? strval($data['admission_mode']) : '';
        }
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
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
        if (isset($data['show_scoreboard'])) {
            $this->show_scoreboard = boolval(
                $data['show_scoreboard']
            );
        }
        if (isset($data['languages'])) {
            $this->languages = is_scalar(
                $data['languages']
            ) ? strval($data['languages']) : '';
        }
        if (isset($data['archived'])) {
            $this->archived = boolval(
                $data['archived']
            );
        }
        if (isset($data['minimum_progress_for_certificate'])) {
            $this->minimum_progress_for_certificate = intval(
                $data['minimum_progress_for_certificate']
            );
        }
        if (isset($data['certificates_status'])) {
            $this->certificates_status = is_scalar(
                $data['certificates_status']
            ) ? strval($data['certificates_status']) : '';
        }
        if (isset($data['recommended'])) {
            $this->recommended = boolval(
                $data['recommended']
            );
        }
        if (isset($data['teaching_assistant_enabled'])) {
            $this->teaching_assistant_enabled = boolval(
                $data['teaching_assistant_enabled']
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
    public $course_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $description = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $objective = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $alias = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $group_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $acl_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $level = null;

    /**
     * Hora de inicio de este curso
     *
     * @var \OmegaUp\Timestamp
     */
    public $start_time;  // 2000-01-01 06:00:00

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $finish_time = null;

    /**
     * Modalidad en la que se registra un curso.
     *
     * @var string
     */
    public $admission_mode = 'private';

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $school_id = null;

    /**
     * Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un curso sólo si ya llenó su información de perfil
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
     * Los estudiantes pueden visualizar el scoreboard de un curso.
     *
     * @var bool
     */
    public $show_scoreboard = false;

    /**
     * Un filtro (opcional) de qué lenguajes se pueden usar en un curso
     *
     * @var string|null
     */
    public $languages = null;

    /**
     * Indica si el curso ha sido archivado por el administrador.
     *
     * @var bool
     */
    public $archived = false;

    /**
     * Progreso mínimo que debe cumplir el estudiante para que se le otorgue el diploma del curso. NULL indica que el curso no da diplomas.
     *
     * @var int|null
     */
    public $minimum_progress_for_certificate = null;

    /**
     * Estado de la petición de generar diplomas
     *
     * @var string
     */
    public $certificates_status = 'uninitiated';

    /**
     * Mostrar el curso en la lista de cursos públicos, los cursos que no tengan la bandera encendida pueden ser cursos públicos pero no se mostrarán en la lista.
     *
     * @var bool
     */
    public $recommended = false;

    /**
     * Indica si el Asistente de enseñanza de IA está habilitado para este curso
     *
     * @var bool
     */
    public $teaching_assistant_enabled = false;
}
