<?php

class CarouselItemsTest extends \OmegaUp\Test\ControllerTestCase {
    private static function localeText(string $en, string $es, string $pt): string {
        return json_encode([
            'en' => $en,
            'es' => $es,
            'pt' => $pt,
        ]);
    }

    public function testUpdatePreservesStatusWhenIsActiveAndStatusOmitted() {
        $adminData = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminData['identity']);

        $title = self::localeText('Problem of the Week', 'Problema de la semana', 'Problema da semana');
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

        $r = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'carousel_item_id' => $carouselItem->carousel_item_id,
            'title' => $title,
            'excerpt' => $excerpt,
            'image_url' => $carouselItem->image_url,
            'link' => $carouselItem->link,
            'button_title' => $buttonTitle,
        ]);

        \OmegaUp\Controllers\CarouselItems::apiUpdate($r);

        $updated = \OmegaUp\DAO\Base\CarouselItems::getByPK($carouselItem->carousel_item_id);
        $this->assertSame('active', $updated->status);
    }
}
