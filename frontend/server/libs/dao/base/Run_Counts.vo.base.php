<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Run_Counts.
 *
 * VO does not have any behaviour.
 * @access public
 */
class RunCounts extends VO {
    /**
     * Constructor de RunCounts
     *
     * Para construir un objeto de tipo RunCounts debera llamarse a el constructor
     * sin parametros. Es posible, construir un objeto pasando como parametro un arreglo asociativo
     * cuyos campos son iguales a las variables que constituyen a este objeto.
     */
    function __construct($data = null) {
        if (is_null($data)) {
            return;
        }
        if (isset($data['date'])) {
            $this->date = $data['date'];
        }
        if (isset($data['total'])) {
            $this->total = $data['total'];
        }
        if (isset($data['ac_count'])) {
            $this->ac_count = $data['ac_count'];
        }
    }

    /**
     * Obtener una representacion en String
     *
     * Este metodo permite tratar a un objeto RunCounts en forma de cadena.
     * La representacion de este objeto en cadena es la forma JSON (JavaScript Object Notation) para este objeto.
     * @return String
     */
    public function __toString() {
        return json_encode([
            'date' => $this->date,
            'total' => $this->total,
            'ac_count' => $this->ac_count,
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
      * @var date
      */
    public $date;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $total;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int(11)
      */
    public $ac_count;
}
