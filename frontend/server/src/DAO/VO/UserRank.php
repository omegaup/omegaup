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
 * Value Object class for table `User_Rank`.
 *
 * @access public
 */
class UserRank extends \OmegaUp\DAO\VO\VO {
    public const FIELD_NAMES = [
        'user_id' => true,
        'ranking' => true,
        'problems_solved_count' => true,
        'score' => true,
        'username' => true,
        'name' => true,
        'country_id' => true,
        'state_id' => true,
        'school_id' => true,
        'author_score' => true,
        'author_ranking' => true,
        'classname' => true,
        'timestamp' => true,
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
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
        }
        if (isset($data['ranking'])) {
            $this->ranking = intval(
                $data['ranking']
            );
        }
        if (isset($data['problems_solved_count'])) {
            $this->problems_solved_count = intval(
                $data['problems_solved_count']
            );
        }
        if (isset($data['score'])) {
            $this->score = floatval(
                $data['score']
            );
        }
        if (isset($data['username'])) {
            $this->username = is_scalar(
                $data['username']
            ) ? strval($data['username']) : '';
        }
        if (isset($data['name'])) {
            $this->name = is_scalar(
                $data['name']
            ) ? strval($data['name']) : '';
        }
        if (isset($data['country_id'])) {
            $this->country_id = is_scalar(
                $data['country_id']
            ) ? strval($data['country_id']) : '';
        }
        if (isset($data['state_id'])) {
            $this->state_id = is_scalar(
                $data['state_id']
            ) ? strval($data['state_id']) : '';
        }
        if (isset($data['school_id'])) {
            $this->school_id = intval(
                $data['school_id']
            );
        }
        if (isset($data['author_score'])) {
            $this->author_score = floatval(
                $data['author_score']
            );
        }
        if (isset($data['author_ranking'])) {
            $this->author_ranking = intval(
                $data['author_ranking']
            );
        }
        if (isset($data['classname'])) {
            $this->classname = is_scalar(
                $data['classname']
            ) ? strval($data['classname']) : '';
        }
        if (isset($data['timestamp'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['timestamp']
             * @var \OmegaUp\Timestamp $this->timestamp
             */
            $this->timestamp = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['timestamp']
                )
            );
        } else {
            $this->timestamp = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
    }

    /**
     * [Campo no documentado]
     * Llave Primaria
     *
     * @var int|null
     */
    public $user_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $ranking = null;

    /**
     * [Campo no documentado]
     *
     * @var int
     */
    public $problems_solved_count = 0;

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $score = 0.00;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $username = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $name = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $country_id = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $state_id = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $school_id = null;

    /**
     * [Campo no documentado]
     *
     * @var float
     */
    public $author_score = 0.00;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $author_ranking = null;

    /**
     * Almacena la clase precalculada para no tener que determinarla en tiempo de ejecucion.
     *
     * @var string|null
     */
    public $classname = null;

    /**
     * Almacena la hora y fecha en que se actualiza el rank de usuario
     *
     * @var \OmegaUp\Timestamp
     */
    public $timestamp;  // CURRENT_TIMESTAMP
}
