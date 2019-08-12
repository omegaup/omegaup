<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table User_Rank_Cutoffs.
 *
 * VO does not have any behaviour.
 * @access public
 */
class UserRankCutoffs extends VO {
    const FIELD_NAMES = [
        'score' => true,
        'percentile' => true,
        'classname' => true,
    ];

    /**
     * Constructor de UserRankCutoffs
     *
     * Para construir un objeto de tipo UserRankCutoffs debera llamarse a el constructor
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
        if (isset($data['score'])) {
            $this->score = (float)$data['score'];
        }
        if (isset($data['percentile'])) {
            $this->percentile = (float)$data['percentile'];
        }
        if (isset($data['classname'])) {
            $this->classname = $data['classname'];
        }
    }

    /**
      *  [Campo no documentado]
      * @access public
      * @var float
     */
    public $score;

    /**
      *  [Campo no documentado]
      * @access public
      * @var float
     */
    public $percentile;

    /**
      *  [Campo no documentado]
      * @access public
      * @var string
     */
    public $classname;
}
