<?php

namespace OmegaUp\DAO;

/**
 * CarouselItems Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link \OmegaUp\DAO\VO\CarouselItems}.
 * @access public
 */
class CarouselItems extends \OmegaUp\DAO\Base\CarouselItems {
   /**
     * Returns all active and non-expired carousel items.
     *
     * @return list<\OmegaUp\DAO\VO\CarouselItems>
     */
    public static function getActiveItems(): array {
        $sql = '
            SELECT
                ' . \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\CarouselItems::FIELD_NAMES,
            'ci'
        ) . '
            FROM
                `Carousel_Items` ci
            WHERE
                ci.status = "active"
                AND (
                    ci.expiration_date IS NULL
                    OR ci.expiration_date >= NOW()
                )
            ORDER BY
                ci.carousel_item_id DESC;
        ';

        /** @var list<array<string, mixed>> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);

        $items = [];
        foreach ($rs as $row) {
            $items[] = new \OmegaUp\DAO\VO\CarouselItems($row);
        }
        return $items;
    }
}
