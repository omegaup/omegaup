<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problems_Tags.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemsTags extends VO {
    /**
     * Constructor de ProblemsTags
     *
     * Para construir un objeto de tipo ProblemsTags debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = $data['problem_id'];
        }
        if (isset($data['tag_id'])) {
            $this->tag_id = $data['tag_id'];
        }
        if (isset($data['public'])) {
            $this->public = $data['public'];
        }
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto ProblemsTags en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() {
        return json_encode([
            'problem_id' => $this->problem_id,
            'tag_id' => $this->tag_id,
            'public' => $this->public,
        ]);
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
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $problem_id;

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var int(11)
      */
    public $tag_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var tinyint(1)
      */
    public $public;
}
