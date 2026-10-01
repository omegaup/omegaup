<?php

class CarouselItemsTest extends \OmegaUp\Test\ControllerTestCase {
    private static function localeText(
        string $en,
        string $es,
        string $pt
    ): string {
        return json_encode([
            'en' => $en,
            'es' => $es,
            'pt' => $pt,
        ]);
    }

    /**
     * Creates an active carousel item and the base update request params.
     *
     * @return array{0: \OmegaUp\DAO\VO\CarouselItems, 1: array<string, mixed>}
     */
    private static function createActiveCarouselItem(): array {
        $adminData = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminData['identity']);

        $title = self::localeText(
            'Problem of the Week',
            'Problema de la semana',
            'Problema da semana'
        );
        $excerpt = self::localeText(
            'A tricky dynamic programming task.',
            'Un tarea de programación dinámica difícil.',
            'Uma tarefa de programação dinâmica difícil.'
        );
        $buttonTitle = self::localeText('Solve it', 'Resolverla', 'Resolver');

        $carouselItem = new \OmegaUp\DAO\VO\CarouselItems([
            'title' => $title,
            'excerpt' => $excerpt,
            'image_url' => 'https://example.com/cover.png',
            'link' => '/problem/ponitorneo',
            'button_title' => $buttonTitle,
            'user_id' => $adminData['user']->user_id,
            'status' => 'active',
        ]);
        \OmegaUp\DAO\Base\CarouselItems::create($carouselItem);

        $baseParams = [
            'auth_token' => $adminLogin->auth_token,
            'carousel_item_id' => $carouselItem->carousel_item_id,
            'title' => $title,
            'excerpt' => $excerpt,
            'image_url' => $carouselItem->image_url,
            'link' => $carouselItem->link,
            'button_title' => $buttonTitle,
        ];

        return [$carouselItem, $baseParams];
    }

    /**
     * Test that status is preserved when is_active and status are omitted
     */
    public function testUpdatePreservesStatusWhenIsActiveAndStatusOmitted() {
        [$carouselItem, $baseParams] = self::createActiveCarouselItem();

        \OmegaUp\Controllers\CarouselItems::apiUpdate(
            new \OmegaUp\Request($baseParams)
        );

        $updated = \OmegaUp\DAO\Base\CarouselItems::getByPK(
            $carouselItem->carousel_item_id
        );
        $this->assertSame('active', $updated->status);
    }

    /**
     * Test that explicitly setting is_active updates the status
     */
    public function testUpdateChangesStatusWhenIsActiveProvided() {
        [$carouselItem, $baseParams] = self::createActiveCarouselItem();

        \OmegaUp\Controllers\CarouselItems::apiUpdate(
            new \OmegaUp\Request(
                array_merge($baseParams, ['is_active' => false])
            )
        );

        $updated = \OmegaUp\DAO\Base\CarouselItems::getByPK(
            $carouselItem->carousel_item_id
        );
        $this->assertSame('inactive', $updated->status);

        \OmegaUp\Controllers\CarouselItems::apiUpdate(
            new \OmegaUp\Request(
                array_merge($baseParams, ['is_active' => true])
            )
        );

        $updated = \OmegaUp\DAO\Base\CarouselItems::getByPK(
            $carouselItem->carousel_item_id
        );
        $this->assertSame('active', $updated->status);
    }

    /**
     * Test that the legacy status alias still updates the status
     */
    public function testUpdateChangesStatusWhenLegacyStatusProvided() {
        [$carouselItem, $baseParams] = self::createActiveCarouselItem();

        \OmegaUp\Controllers\CarouselItems::apiUpdate(
            new \OmegaUp\Request(
                array_merge($baseParams, ['status' => false])
            )
        );

        $updated = \OmegaUp\DAO\Base\CarouselItems::getByPK(
            $carouselItem->carousel_item_id
        );
        $this->assertSame('inactive', $updated->status);

        \OmegaUp\Controllers\CarouselItems::apiUpdate(
            new \OmegaUp\Request(
                array_merge($baseParams, ['status' => true])
            )
        );

        $updated = \OmegaUp\DAO\Base\CarouselItems::getByPK(
            $carouselItem->carousel_item_id
        );
        $this->assertSame('active', $updated->status);
    }
}
