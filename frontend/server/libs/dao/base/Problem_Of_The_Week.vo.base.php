<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problem_Of_The_Week.
 *
 * VO does not have any behaviour.
 * @access public
 */
class ProblemOfTheWeek extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'problem_of_the_week_id' => true,
        'problem_id' => true,
        'time' => true,
        'difficulty' => true,
    ];

    /**
     * Constructor de ProblemOfTheWeek
     *
     * Para construir un objeto de tipo ProblemOfTheWeek debera llamarse a el constructor
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
        if (isset($data['problem_of_the_week_id'])) {
            $this->problem_of_the_week_id = (int)$data['problem_of_the_week_id'];
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['time'])) {
            $this->time = strval($data['time']);
        }
        if (isset($data['difficulty'])) {
            $this->difficulty = strval($data['difficulty']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $problem_of_the_week_id = 0;

    /**
     * El id del problema escogido como problema de la semana.
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * El inicio de la semana de la cual este problema fue elegido como el mejor de la semana.
     *
     * @var string
     */
    public $time = '2000-01-01';

    /**
     * En algún momento tendremos un problema fácil y uno difícil.
     *
     * @var string|null
     */
    public $difficulty = null;
}
