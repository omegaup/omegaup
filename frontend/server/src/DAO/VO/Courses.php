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
        'alias' => true,
        'group_id' => true,
        'acl_id' => true,
        'start_time' => true,
        'finish_time' => true,
        'public' => true,
        'school_id' => true,
        'needs_basic_information' => true,
        'requests_user_information' => true,
        'show_scoreboard' => true,
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
            $this->name = strval(
                $data['name']
            );
        }
        if (isset($data['description'])) {
            $this->description = strval(
                $data['description']
            );
        }
        if (isset($data['alias'])) {
            $this->alias = strval(
                $data['alias']
            );
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
        if (isset($data['start_time'])) {
            /**
             * @var string|int|float $data['start_time']
             * @var int $this->start_time
             */
            $this->start_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['start_time']
                )
            );
        }
        if (isset($data['finish_time'])) {
            /**
             * @var string|int|float $data['finish_time']
             * @var int $this->finish_time
             */
            $this->finish_time = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['finish_time']
                )
            );
        }
        if (isset($data['public'])) {
            $this->public = boolval(
                $data['public']
            );
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
            $this->requests_user_information = strval(
                $data['requests_user_information']
            );
        }
        if (isset($data['show_scoreboard'])) {
            $this->show_scoreboard = boolval(
                $data['show_scoreboard']
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
     * Hora de inicio de este curso
     *
     * @var int
     */
    public $start_time = 946706400; // 2000-01-01 06:00:00

    /**
     * Hora de finalizacion de este curso
     *
     * @var int
     */
    public $finish_time = 946706400; // 2000-01-01 06:00:00

    /**
     * True implica que cualquier usuario puede entrar al curso
     *
     * @var bool
     */
    public $public = false;

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
}
