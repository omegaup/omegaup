<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Courses.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Courses extends VO {
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

    /**
     * Constructor de Courses
     *
     * Para construir un objeto de tipo Courses debera llamarse a el constructor
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
        if (isset($data['course_id'])) {
            $this->course_id = (int)$data['course_id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['alias'])) {
            $this->alias = $data['alias'];
        }
        if (isset($data['group_id'])) {
            $this->group_id = (int)$data['group_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = (int)$data['acl_id'];
        }
        if (isset($data['start_time'])) {
            $this->start_time = DAO::fromMySQLTimestamp($data['start_time']);
        }
        if (isset($data['finish_time'])) {
            $this->finish_time = DAO::fromMySQLTimestamp($data['finish_time']);
        }
        if (isset($data['public'])) {
            $this->public = boolval($data['public']);
        }
        if (isset($data['school_id'])) {
            $this->school_id = (int)$data['school_id'];
        }
        if (isset($data['needs_basic_information'])) {
            $this->needs_basic_information = boolval($data['needs_basic_information']);
        }
        if (isset($data['requests_user_information'])) {
            $this->requests_user_information = $data['requests_user_information'];
        }
        if (isset($data['show_scoreboard'])) {
            $this->show_scoreboard = boolval($data['show_scoreboard']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $course_id = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $description;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $alias;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $group_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $acl_id;

    /**
      * Hora de inicio de este curso
      * @access public
      * @var int
     */
    public $start_time = 946706400; // 2000-01-01 06:00:00

    /**
      * Hora de finalizacion de este curso
      * @access public
      * @var int
     */
    public $finish_time = 946706400; // 2000-01-01 06:00:00

    /**
      * True implica que cualquier usuario puede entrar al curso
      * @access public
      * @var bool
     */
    public $public = false;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?int
     */
    public $school_id;

    /**
      * Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un curso sólo si ya llenó su información de perfil
      * @access public
      * @var bool
     */
    public $needs_basic_information = false;

    /**
      * Se solicita información de los participantes para contactarlos posteriormente.
      * @access public
      * @var string
     */
    public $requests_user_information = 'no';

    /**
      * Los estudiantes pueden visualizar el scoreboard de un curso.
      * @access public
      * @var bool
     */
    public $show_scoreboard = false;
}
