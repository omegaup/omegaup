<?php

/** ******************************************************************************* *
  *                    !ATENCION!                                                   *
  *                                                                                 *
  * Este codigo es generado automaticamente. Si lo modificas tus cambios seran      *
  * reemplazados la proxima vez que se autogenere el codigo.                        *
  *                                                                                 *
  * ******************************************************************************* */

/**
 * Value Object file for table Problems.
 *
 * VO does not have any behaviour.
 * @access public
 */
class Problems extends VO {
    const FIELD_NAMES = [
        'problem_id' => true,
        'acl_id' => true,
        'visibility' => true,
        'title' => true,
        'alias' => true,
        'commit' => true,
        'current_version' => true,
        'languages' => true,
        'input_limit' => true,
        'visits' => true,
        'submissions' => true,
        'accepted' => true,
        'difficulty' => true,
        'creation_date' => true,
        'source' => true,
        'order' => true,
        'deprecated' => true,
        'email_clarifications' => true,
        'quality' => true,
        'quality_histogram' => true,
        'difficulty_histogram' => true,
    ];

    /**
     * Constructor de Problems
     *
     * Para construir un objeto de tipo Problems debera llamarse a el constructor
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
        if (isset($data['problem_id'])) {
            $this->problem_id = (int)$data['problem_id'];
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = (int)$data['acl_id'];
        }
        if (isset($data['visibility'])) {
            $this->visibility = (int)$data['visibility'];
        }
        if (isset($data['title'])) {
            $this->title = strval($data['title']);
        }
        if (isset($data['alias'])) {
            $this->alias = strval($data['alias']);
        }
        if (isset($data['commit'])) {
            $this->commit = strval($data['commit']);
        }
        if (isset($data['current_version'])) {
            $this->current_version = strval($data['current_version']);
        }
        if (isset($data['languages'])) {
            $this->languages = strval($data['languages']);
        }
        if (isset($data['input_limit'])) {
            $this->input_limit = (int)$data['input_limit'];
        }
        if (isset($data['visits'])) {
            $this->visits = (int)$data['visits'];
        }
        if (isset($data['submissions'])) {
            $this->submissions = (int)$data['submissions'];
        }
        if (isset($data['accepted'])) {
            $this->accepted = (int)$data['accepted'];
        }
        if (isset($data['difficulty'])) {
            $this->difficulty = (float)$data['difficulty'];
        }
        if (isset($data['creation_date'])) {
            /**
             * @var string|int|float $data['creation_date']
             * @var int $this->creation_date
             */
            $this->creation_date = DAO::fromMySQLTimestamp($data['creation_date']);
        } else {
            $this->creation_date = Time::get();
        }
        if (isset($data['source'])) {
            $this->source = strval($data['source']);
        }
        if (isset($data['order'])) {
            $this->order = strval($data['order']);
        }
        if (isset($data['deprecated'])) {
            $this->deprecated = boolval($data['deprecated']);
        }
        if (isset($data['email_clarifications'])) {
            $this->email_clarifications = boolval($data['email_clarifications']);
        }
        if (isset($data['quality'])) {
            $this->quality = (float)$data['quality'];
        }
        if (isset($data['quality_histogram'])) {
            $this->quality_histogram = strval($data['quality_histogram']);
        }
        if (isset($data['difficulty_histogram'])) {
            $this->difficulty_histogram = strval($data['difficulty_histogram']);
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     * Auto Incremento
     *
     * @var int|null
     */
    public $problem_id = 0;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $acl_id = null;

    /**
     * -1 banned, 0 private, 1 public, 2 recommended
     *
     * @var int
     */
    public $visibility = 1;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $title = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $alias = null;

    /**
     * El hash SHA1 del commit en la rama master del problema.
     *
     * @var string
     */
    public $commit = 'published';

    /**
     * El hash SHA1 del árbol de la rama private.
     *
     * @var string|null
     */
    public $current_version = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $languages = 'c,cpp,java,py,rb,pl,cs,pas,hs,cpp11,lua';

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $input_limit = 10240;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $visits = 0;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $submissions = 0;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $accepted = 0;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $difficulty = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $creation_date;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $source = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $order = 'normal';

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $deprecated = false;

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $email_clarifications = false;

    /**
     * [Campo no documentado]
     *
     * @var float|null
     */
    public $quality = null;

    /**
     * Valores del histograma de calidad del problema.
     *
     * @var string|null
     */
    public $quality_histogram = null;

    /**
     * Valores del histograma de dificultad del problema.
     *
     * @var string|null
     */
    public $difficulty_histogram = null;
}
