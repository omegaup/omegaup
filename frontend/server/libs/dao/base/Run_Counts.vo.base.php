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
    const FIELD_NAMES = [
        'date' => true,
        'total' => true,
        'ac_count' => true,
    ];

    /**
     * Constructor de RunCounts
     *
     * Para construir un objeto de tipo RunCounts debera llamarse a el constructor
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
        if (isset($data['date'])) {
            $this->date = $data['date'];
        }
        if (isset($data['total'])) {
            $this->total = (int)$data['total'];
        }
        if (isset($data['ac_count'])) {
            $this->ac_count = (int)$data['ac_count'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * @access public
      * @var string
     */
    public $date;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $total = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $ac_count = 0;
}
