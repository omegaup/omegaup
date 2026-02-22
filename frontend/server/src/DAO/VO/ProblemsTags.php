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
 * Value Object class for table `Problems_Tags`.
 *
 * @access public
 */
class ProblemsTags extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'problem_id' => true,
        'tag_id' => true,
        'source' => true,
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
        if (isset($data['tag_id'])) {
            $this->tag_id = intval(
                $data['tag_id']
            );
        }
        if (isset($data['source'])) {
            $this->source = is_scalar(
                $data['source']
            ) ? strval($data['source']) : '';
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $problem_id = null;

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $tag_id = null;

    /**
     * El origen del tag: elegido por el autor, elegido por los usuarios o elegido por un revisor.
     *
     * @var string
     */
    public $source = 'owner';
}
