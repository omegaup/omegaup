<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return [
            'smartyProperties' => [],
            'template' => 'login.password.recover.tpl',
        ];
    }
);
//require_once('../server/bootstrap_smarty.php');
//$smarty->display('../templates/login.password.recover.tpl');
