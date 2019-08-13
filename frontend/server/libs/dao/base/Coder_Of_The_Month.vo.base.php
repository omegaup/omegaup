<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Coder_Of_The_Month.
 *
 * VO does not have any behaviour.
 * @access public
 */
class CoderOfTheMonth extends VO {
    const FIELD_NAMES = [
        'coder_of_the_month_id' => true,
        'user_id' => true,
        'description' => true,
        'time' => true,
        'interview_url' => true,
        'rank' => true,
        'selected_by' => true,
    ];

    /**
     * Constructor de CoderOfTheMonth
     *
     * Para construir un objeto de tipo CoderOfTheMonth debera llamarse a el constructor
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
        if (isset($data['coder_of_the_month_id'])) {
            $this->coder_of_the_month_id = (int)$data['coder_of_the_month_id'];
        }
        if (isset($data['user_id'])) {
            $this->user_id = (int)$data['user_id'];
        }
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['time'])) {
            $this->time = $data['time'];
        }
        if (isset($data['interview_url'])) {
            $this->interview_url = $data['interview_url'];
        }
        if (isset($data['rank'])) {
            $this->rank = (int)$data['rank'];
        }
        if (isset($data['selected_by'])) {
            $this->selected_by = (int)$data['selected_by'];
        }
    }

    /**
      *  [Campo no documentado]
      * Llave Primaria
      * Auto Incremento
      * @access public
      * @var int
     */
    public $coder_of_the_month_id = 0;

    /**
      *  [Campo no documentado]
      * @access public
      * @var int
     */
    public $user_id;

    /**
      *  [Campo no documentado]
      * @access public
      * @var ?string
     */
    public $description;

    /**
      * Fecha no es UNIQUE por si hay más de 1 coder de mes.
      * @access public
      * @var string
     */
    public $time = '2000-01-01';

    /**
      * Para linekar a un post del blog con entrevistas.
      * @access public
      * @var ?string
     */
    public $interview_url;

    /**
      * El lugar en el que el usuario estuvo durante ese mes
      * @access public
      * @var int
     */
    public $rank;

    /**
      * Id de la identidad que seleccionó al coder.
      * @access public
      * @var ?int
     */
    public $selected_by;
}
