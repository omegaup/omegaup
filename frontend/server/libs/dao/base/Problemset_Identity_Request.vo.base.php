<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemset_Identity_Request.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsetIdentityRequest extends VO {
    /**
     * Constructor de ProblemsetIdentityRequest
     *
     * Para construir un objeto de tipo ProblemsetIdentityRequest debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct(?array $data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['identity_id'])) {
            $this->identity_id = (int)$data['identity_id'];
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = (int)$data['problemset_id'];
        }
        if (isset($data['request_time'])) {
            $this->request_time = $data['request_time'];
        }
        if (isset($data['last_update'])) {
            $this->last_update = $data['last_update'];
        }
        if (isset($data['accepted'])) {
            $this->accepted = boolval($data['accepted']);
        }
        if (isset($data['extra_note'])) {
            $this->extra_note = $data['extra_note'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(iterable $fields = []) : void {
        if (empty($fields)) {
            parent::toUnixTime(['request_time', 'last_update']);
            return;
        }
        parent::toUnixTime($fields);
    }

    /**
      * Identidad del usuario
      * Llave Primaria
      * @access public
      * @var int
     */
    public $identity_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int
     */
    public $problemset_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $request_time = null;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $last_update;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?bool
     */
    public $accepted;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $extra_note;
}
