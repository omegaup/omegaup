<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Interviews.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Interviews extends VO {
    const FIELD_NAMES = [
        'interview_id' => true,
        'problemset_id' => true,
        'acl_id' => true,
        'alias' => true,
        'title' => true,
        'description' => true,
        'window_length' => true,
    ];

    /**
     * Constructor de Interviews
     *
     * Para construir un objeto de tipo Interviews debera llamarse a el constructor
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
        if (isset($data['interview_id'])) {
            $this->interview_id = (int)$data['interview_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = (int)$data['acl_id'];
        }
        if (isset($data['alias'])) {
            $this->alias = $data['alias'];
        }
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['window_length'])) {
            $this->window_length = (int)$data['window_length'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $interview_id = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $problemset_id;

    /**
      * La lista de control de acceso del problema
      * @access public
      * @var int
     */
    public $acl_id;

    /**
      * El alias de la entrevista
      * @access public
      * @var string
     */
    public $alias;

    /**
      * El titulo de la entrevista.
      * @access public
      * @var string
     */
    public $title;

    /**
      * Una breve descripcion de la entrevista.
      * @access public
      * @var string
     */
    public $description;

    /**
      * Indica el tiempo que tiene el usuario para envíar soluciones.
      * @access public
      * @var int
     */
    public $window_length;
}
