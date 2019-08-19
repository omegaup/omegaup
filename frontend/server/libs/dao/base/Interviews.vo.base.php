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
            $this->alias = strval($data['alias']);
        }
        if (isset($data['title'])) {
            $this->title = strval($data['title']);
        }
        if (isset($data['description'])) {
            $this->description = strval($data['description']);
        }
        if (isset($data['window_length'])) {
            $this->window_length = (int)$data['window_length'];
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $interview_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $problemset_id = null;

    /**
     * La lista de control de acceso del problema
     *
     * @var int|null
     */
    public $acl_id = null;

    /**
     * El alias de la entrevista
     *
     * @var string|null
     */
    public $alias = null;

    /**
     * El titulo de la entrevista.
     *
     * @var string|null
     */
    public $title = null;

    /**
     * Una breve descripcion de la entrevista.
     *
     * @var string|null
     */
    public $description = null;

    /**
     * Indica el tiempo que tiene el usuario para envíar soluciones.
     *
     * @var int|null
     */
    public $window_length = null;
}
