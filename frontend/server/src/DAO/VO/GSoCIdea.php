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
 * Value Object class for table `GSoC_Idea`.
 *
 * @access public
 */
class GSoCIdea extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'idea_id' => true,
        'title' => true,
        'brief_description' => true,
        'expected_results' => true,
        'preferred_skills' => true,
        'possible_mentors' => true,
        'estimated_hours' => true,
        'skill_level' => true,
        'blog_link' => true,
        'contributor_username' => true,
        'created_at' => true,
        'updated_at' => true,
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
        if (isset($data['idea_id'])) {
            $this->idea_id = intval(
                $data['idea_id']
            );
        }
        if (isset($data['title'])) {
            $this->title = is_scalar(
                $data['title']
            ) ? strval($data['title']) : '';
        }
        if (isset($data['brief_description'])) {
            $this->brief_description = is_scalar(
                $data['brief_description']
            ) ? strval($data['brief_description']) : '';
        }
        if (isset($data['expected_results'])) {
            $this->expected_results = is_scalar(
                $data['expected_results']
            ) ? strval($data['expected_results']) : '';
        }
        if (isset($data['preferred_skills'])) {
            $this->preferred_skills = is_scalar(
                $data['preferred_skills']
            ) ? strval($data['preferred_skills']) : '';
        }
        if (isset($data['possible_mentors'])) {
            $this->possible_mentors = is_scalar(
                $data['possible_mentors']
            ) ? strval($data['possible_mentors']) : '';
        }
        if (isset($data['estimated_hours'])) {
            $this->estimated_hours = intval(
                $data['estimated_hours']
            );
        }
        if (isset($data['skill_level'])) {
            $this->skill_level = is_scalar(
                $data['skill_level']
            ) ? strval($data['skill_level']) : '';
        }
        if (isset($data['blog_link'])) {
            $this->blog_link = is_scalar(
                $data['blog_link']
            ) ? strval($data['blog_link']) : '';
        }
        if (isset($data['contributor_username'])) {
            $this->contributor_username = is_scalar(
                $data['contributor_username']
            ) ? strval($data['contributor_username']) : '';
        }
        if (isset($data['created_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['created_at']
             * @var \OmegaUp\Timestamp $this->created_at
             */
            $this->created_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['created_at']
                )
            );
        } else {
            $this->created_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
            );
        }
        if (isset($data['updated_at'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['updated_at']
             * @var \OmegaUp\Timestamp $this->updated_at
             */
            $this->updated_at = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['updated_at']
                )
            );
        } else {
            $this->updated_at = new \OmegaUp\Timestamp(
                \OmegaUp\Time::get()
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
    public $idea_id = 0;

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
    public $brief_description = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $expected_results = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $preferred_skills = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $possible_mentors = null;

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $estimated_hours = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $skill_level = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $blog_link = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $contributor_username = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $created_at;  // CURRENT_TIMESTAMP

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp
     */
    public $updated_at;  // CURRENT_TIMESTAMP
}
