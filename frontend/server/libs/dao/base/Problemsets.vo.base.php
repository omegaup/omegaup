<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problemsets.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Problemsets extends VO {
    /**
     * Constructor de Problemsets
     *
     * Para construir un objeto de tipo Problemsets debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['problemset_id'])) {
            $this->problemset_id = $data['problemset_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = $data['acl_id'];
        }
        if (isset($data['access_mode'])) {
            $this->access_mode = $data['access_mode'];
        }
        if (isset($data['languages'])) {
            $this->languages = $data['languages'];
        }
    }

    /**
     * Converts date fields to timestamps
     */
    public function toUnixTime(array $fields = []) {
        if (count($fields) > 0) {
            parent::toUnixTime($fields);
        } else {
            parent::toUnixTime([]);
        }
    }

    /**
      * El identificador único para cada conjunto de problemas
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int(11)
      */
    public $problemset_id;

    /**
      * La lista de control de acceso compartida con su container
      * @access public
      * @var int(11)
      */
    public $acl_id;

    /**
      * La modalidad de acceso a este conjunto de problemas
      * @access public
      * @var enum('private','public','registration')
      */
    public $access_mode;

    /**
      * Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas
      * @access public
      * @var set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua')
      */
    public $languages;
}
