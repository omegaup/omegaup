<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Assignments.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Assignments extends VO {
    /**
     * Constructor de Assignments
     *
     * Para construir un objeto de tipo Assignments debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['assignment_id'])) {
            $this->assignment_id = $data['assignment_id'];
        }
        if (isset($data['course_id'])) {
            $this->course_id = $data['course_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = $data['acl_id'];
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
        if (isset($data['publish_time_delay'])) {
            $this->publish_time_delay = $data['publish_time_delay'];
        }
        if (isset($data['assignment_type'])) {
            $this->assignment_type = $data['assignment_type'];
        }
        if (isset($data['start_time'])) {
            $this->start_time = $data['start_time'];
        }
        if (isset($data['finish_time'])) {
            $this->finish_time = $data['finish_time'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime(['start_time', 'finish_time']);
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $assignment_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $course_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      * La lista de control de acceso compartida con el curso
      * @access public
      * @var int(11)
      */
    public $acl_id;

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
      * @var int(11),
      */
    public $publish_time_delay;

    /**
      *  [Campo no documentado]
      * @access public
      * @var enum('homework',
      */
    public $assignment_type;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $start_time;

    /**
      *  [Campo no documentado]
      * @access public
      * @var timestamp
      */
    public $finish_time;
}
