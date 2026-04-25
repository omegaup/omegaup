<?php
/** ************************************************************************ *
 *                    !ATENCION!                                             *
 *                                                                           *
 * Este codigo es generado automáticamente. Si lo modificas, tus cambios     *
 * serán reemplazados la proxima vez que se autogenere el código.            *
 *                                                                           *
 * ************************************************************************* */

namespace OmegaUp\DAO\VO;

/**
 * Value Object class for table `Problems`.
 *
 * @access public
 */
class Problems extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
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
        'quality_seal' => true,
        'show_diff' => true,
        'allow_user_add_tags' => true,
    ];

    public function __construct(?array $data = null) {
        if (empty($data)) {
            return;
        }
        $unknownColumns = array_diff_key($data, self::FIELD_NAMES);
        if (!empty($unknownColumns)) {
            throw new \Exception(
                'Unknown columns: ' . join(', ', array_keys($unknownColumns))
            );
        }
        if (isset($data['problem_id'])) {
            $this->problem_id = intval(
                $data['problem_id']
            );
        }
        if (isset($data['acl_id'])) {
            $this->acl_id = intval(
                $data['acl_id']
            );
        }
        if (isset($data['visibility'])) {
            $this->visibility = intval(
                $data['visibility']
            );
        }
        if (isset($data['title'])) {
            $this->title = is_scalar(
                $data['title']
            ) ? strval($data['title']) : '';
        }
        if (isset($data['alias'])) {
            $this->alias = is_scalar(
                $data['alias']
            ) ? strval($data['alias']) : '';
        }
        if (isset($data['commit'])) {
            $this->commit = is_scalar(
                $data['commit']
            ) ? strval($data['commit']) : '';
        }
        if (isset($data['current_version'])) {
            $this->current_version = is_scalar(
                $data['current_version']
            ) ? strval($data['current_version']) : '';
        }
        if (isset($data['languages'])) {
            $this->languages = is_scalar(
                $data['languages']
            ) ? strval($data['languages']) : '';
        }
        if (isset($data['input_limit'])) {
            $this->input_limit = intval(
                $data['input_limit']
            );
        }
        if (isset($data['visits'])) {
            $this->visits = intval(
                $data['visits']
            );
        }
        if (isset($data['submissions'])) {
            $this->submissions = intval(
                $data['submissions']
            );
        }
        if (isset($data['accepted'])) {
            $this->accepted = intval(
                $data['accepted']
            );
        }
        if (isset($data['difficulty'])) {
            $this->difficulty = floatval(
                $data['difficulty']
            );
        }
        if (isset($data['creation_date'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['creation_date']
             * @var \OmegaUp\Timestamp $this->creation_date
             */
            $this->creation_date = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['creation_date']
                )
            );
        } else {
            $this->creation_date = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['source'])) {
            $this->source = is_scalar(
                $data['source']
            ) ? strval($data['source']) : '';
        }
        if (isset($data['order'])) {
            $this->order = is_scalar(
                $data['order']
            ) ? strval($data['order']) : '';
        }
        if (isset($data['deprecated'])) {
            $this->deprecated = boolval(
                $data['deprecated']
            );
        }
        if (isset($data['email_clarifications'])) {
            $this->email_clarifications = boolval(
                $data['email_clarifications']
            );
        }
        if (isset($data['quality'])) {
            $this->quality = floatval(
                $data['quality']
            );
        }
        if (isset($data['quality_histogram'])) {
            $this->quality_histogram = is_scalar(
                $data['quality_histogram']
            ) ? strval($data['quality_histogram']) : '';
        }
        if (isset($data['difficulty_histogram'])) {
            $this->difficulty_histogram = is_scalar(
                $data['difficulty_histogram']
            ) ? strval($data['difficulty_histogram']) : '';
        }
        if (isset($data['quality_seal'])) {
            $this->quality_seal = boolval(
                $data['quality_seal']
            );
        }
        if (isset($data['show_diff'])) {
            $this->show_diff = is_scalar(
                $data['show_diff']
            ) ? strval($data['show_diff']) : '';
        }
        if (isset($data['allow_user_add_tags'])) {
            $this->allow_user_add_tags = boolval(
                $data['allow_user_add_tags']
            );
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
    public $languages = 'c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js';

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
     * @var \OmegaUp\Timestamp
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

    /**
     * [Campo no documentado]
     *
     * @var bool
     */
    public $quality_seal = false;

    /**
     * Determina si el problema es educativo y debe mostrar diferencias en casos de ejemplos, en todos o en ninguno.
     *
     * @var string
     */
    public $show_diff = 'none';

    /**
     * Bandera que sirve para indicar si un problema puede permitir que los usuarios agreguen tags.
     *
     * @var bool
     */
    public $allow_user_add_tags = true;
}
