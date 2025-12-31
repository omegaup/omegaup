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
 * Value Object class for table `Carousel_Items`.
 *
 * @access public
 */
class CarouselItems extends \OmegaUp\DAO\VO\VO {
    const FIELD_NAMES = [
        'carousel_item_id' => true,
        'title' => true,
        'excerpt' => true,
        'image_url' => true,
        'link' => true,
        'button_title' => true,
        'expiration_date' => true,
        'status' => true,
        'user_id' => true,
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
        if (isset($data['carousel_item_id'])) {
            $this->carousel_item_id = intval(
                $data['carousel_item_id']
            );
        }
        if (isset($data['title'])) {
            $this->title = is_scalar(
                $data['title']
            ) ? strval($data['title']) : '';
        }
        if (isset($data['excerpt'])) {
            $this->excerpt = is_scalar(
                $data['excerpt']
            ) ? strval($data['excerpt']) : '';
        }
        if (isset($data['image_url'])) {
            $this->image_url = is_scalar(
                $data['image_url']
            ) ? strval($data['image_url']) : '';
        }
        if (isset($data['link'])) {
            $this->link = is_scalar(
                $data['link']
            ) ? strval($data['link']) : '';
        }
        if (isset($data['button_title'])) {
            $this->button_title = is_scalar(
                $data['button_title']
            ) ? strval($data['button_title']) : '';
        }
        if (isset($data['expiration_date'])) {
            /**
             * @var \OmegaUp\Timestamp|string|int|float $data['expiration_date']
             * @var \OmegaUp\Timestamp $this->expiration_date
             */
            $this->expiration_date = (
                \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                    $data['expiration_date']
                )
            );
        }
        if (isset($data['status'])) {
            $this->status = is_scalar(
                $data['status']
            ) ? strval($data['status']) : '';
        }
        if (isset($data['user_id'])) {
            $this->user_id = intval(
                $data['user_id']
            );
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
    public $carousel_item_id = 0;

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
    public $excerpt = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $image_url = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $link = null;

    /**
     * [Campo no documentado]
     *
     * @var string|null
     */
    public $button_title = null;

    /**
     * [Campo no documentado]
     *
     * @var \OmegaUp\Timestamp|null
     */
    public $expiration_date = null;

    /**
     * [Campo no documentado]
     *
     * @var string
     */
    public $status = 'active';

    /**
     * [Campo no documentado]
     *
     * @var int|null
     */
    public $user_id = null;

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
