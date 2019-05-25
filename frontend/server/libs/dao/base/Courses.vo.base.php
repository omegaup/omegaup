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
    /**
     * Constructor de Courses
     *
     * Para construir un objeto de tipo Courses debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
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
            $this->start_time = $data['start_time'];
        }
        if (isset($data['finish_time'])) {
            $this->finish_time = $data['finish_time'];
        }
        if (isset($data['public'])) {
            $this->public = $data['public'] == '1';
        }
        if (isset($data['school_id'])) {
            $this->school_id = (int)$data['school_id'];
        }
        if (isset($data['needs_basic_information'])) {
            $this->needs_basic_information = $data['needs_basic_information'] == '1';
        }
        if (isset($data['requests_user_information'])) {
            $this->requests_user_information = $data['requests_user_information'];
        }
        if (isset($data['show_scoreboard'])) {
            $this->show_scoreboard = $data['show_scoreboard'] == '1';
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (empty($fields)) {
            parent::toUnixTime(['start_time', 'finish_time']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $course_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(100)
      */
    public $name;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinytext
      */
    public $description;

    /**
      *  [Campo no documentado]
      * @access public
      * @var varchar(32)
      */
    public $alias;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $group_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $acl_id;

    /**
      * Hora de inicio de este curso
      * @access public
      * @var timestamp
      */
    public $start_time;

    /**
      * Hora de finalizacion de este curso
      * @access public
      * @var timestamp
      */
    public $finish_time;

    /**
      * True implica que cualquier usuario puede entrar al curso
      * @access public
      * @var tinyint(1)
      */
    public $public;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $school_id;

    /**
      * Un campo opcional para indicar si es obligatorio que el usuario pueda ingresar a un curso sólo si ya llenó su información de perfil
      * @access public
      * @var tinyint(1)
      */
    public $needs_basic_information;

    /**
      * Se solicita información de los participantes para contactarlos posteriormente.
      * @access public
      * @var enum('no','optional','required')
      */
    public $requests_user_information;

    /**
      * Los estudiantes pueden visualizar el scoreboard de un curso.
      * @access public
      * @var tinyint(1)
      */
    public $show_scoreboard;
}
