<?php

namespace OmegaUp\Controllers;

/**
 * CarouselItemController
 *
 * @psalm-type CarouselItem=array{ corousel_item_id: int, title: string, excerpt: string, image_url: string, link: string, button_title: string, expiration_date: \OmegaUp\Timestamp|null, status: bool}
 * @psalm-type CarouselItemListPayload=array{carouselItems: list<CarouselItem>}
 */
class CarouselItems extends \OmegaUp\Controllers\Controller {
    /**
     * Create a new Carousel Item
     *
     * @omegaup-request-param string $title
     * @omegaup-request-param string $excerpt
     * @omegaup-request-param string $image_url
     * @omegaup-request-param string $link
     * @omegaup-request-param string $buttonTitle
     * @omegaup-request-param null|string $expiration_date
     * @omegaup-request-param bool $status
     *
     * @return array{status: string}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        self::validateAdmin($r->identity);

        $expiration = $r->ensureOptionalString('expiration_date');
        $carouselItem = new \OmegaUp\DAO\VO\CarouselItems([
            'title' => $r->ensureString('title'),
            'excerpt' => $r->ensureString('excerpt'),
            'image_url' => $r->ensureString('image_url'),
            'link' => $r->ensureString('link'),
            'button_title' => $r->ensureString('buttonTitle'),
            'expiration_date' => is_null($expiration)
                ? null
                : new \OmegaUp\Timestamp(strtotime($expiration)),
            'status' => $r->ensureBool('status'),
        ]);

        \OmegaUp\DAO\Base\CarouselItems::create($carouselItem);
        return ['status' => 'ok'];
    }

    /**
     * Delete a Carousel Item
     *
     * @omegaup-request-param int $corousel_item_id
     *
     * @return array{status: string}
     */
    public static function apiDelete(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        self::validateAdmin($r->identity);

        $carouselItemId = $r->ensureInt('corousel_item_id');
        $carouselItem = \OmegaUp\DAO\Base\CarouselItems::getByPK(
            $carouselItemId
        );
        if (is_null($carouselItem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'carouselItemNotFound'
            );
        }

        \OmegaUp\DAO\Base\CarouselItems::delete($carouselItem);
        return ['status' => 'ok'];
    }

    /**
     * Update a Carousel Item
     *
     * @omegaup-request-param int $corousel_item_id
     * @omegaup-request-param string $title
     * @omegaup-request-param string $excerpt
     * @omegaup-request-param string $image_url
     * @omegaup-request-param string $link
     * @omegaup-request-param string $buttonTitle
     * @omegaup-request-param null|string $expiration_date
     * @omegaup-request-param bool $status
     *
     * @return array{status: string}
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        self::validateAdmin($r->identity);

        $carouselItem = \OmegaUp\DAO\Base\CarouselItems::getByPK(
            $r->ensureInt('corousel_item_id')
        );
        if (is_null($carouselItem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'carouselItemNotFound'
            );
        }

        $carouselItem->title = $r->ensureString('title');
        $carouselItem->excerpt = $r->ensureString('excerpt');
        $carouselItem->image_url = $r->ensureString('image_url');
        $carouselItem->link = $r->ensureString('link');
        $carouselItem->button_title = $r->ensureString('buttonTitle');

        $expiration = $r->ensureOptionalString('expiration_date');
        $carouselItem->expiration_date = is_null($expiration)
            ? null
            : new \OmegaUp\Timestamp(strtotime($expiration));

        $carouselItem->status = $r['status'] ? 'true' : 'false';

        \OmegaUp\DAO\Base\CarouselItems::update($carouselItem);
        return ['status' => 'ok'];
    }

    /**
     * List all Carousel Items (admin only)
     *
     * @return CarouselItemListPayload
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        self::validateAdmin($r->identity);

        return [
            'carouselItems' => array_map(
                fn(\OmegaUp\DAO\VO\CarouselItems $item): array => [
                    'corousel_item_id' => $item->corousel_item_id ?? 0,
                    'title' => $item->title ?? '',
                    'excerpt' => $item->excerpt ?? '',
                    'image_url' => $item->image_url ?? '',
                    'link' => $item->link ?? '',
                    'button_title' => $item->button_title ?? '',
                    'expiration_date' => $item->expiration_date,
                    'status' => boolval($item->status)
                ],
                \OmegaUp\DAO\Base\CarouselItems::getAll()
            ),
        ];
    }

    /**
     * List all status Carousel Items (homepage)
     *
     * @return CarouselItemListPayload
     */
    public static function apiListActive(\OmegaUp\Request $r): array {
        $allItems = \OmegaUp\DAO\Base\CarouselItems::getAll();
        $now = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        $activeItems = array_filter($allItems, function ($item) use ($now) {
            /** @var \OmegaUp\DAO\VO\CarouselItems $item */
            return boolval($item->status) && (
                is_null($item->expiration_date) || $item->expiration_date >= $now
            );
        });

        return [
            'carouselItems' => array_map(
                fn(\OmegaUp\DAO\VO\CarouselItems $item): array => [
                    'corousel_item_id' => $item->corousel_item_id ?? 0,
                    'title' => $item->title ?? '',
                    'excerpt' => $item->excerpt ?? '',
                    'image_url' => $item->image_url ?? '',
                    'link' => $item->link ?? '',
                    'button_title' => $item->button_title ?? '',
                    'expiration_date' => $item->expiration_date,
                    'status' => boolval($item->status)
                ],
                array_values($activeItems)
            ),
        ];
    }

    /**
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateAdmin(\OmegaUp\DAO\VO\Identities $identity): void {
        if (!\OmegaUp\Authorization::isSystemAdmin($identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
    }
}
