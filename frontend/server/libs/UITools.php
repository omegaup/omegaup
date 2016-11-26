<?php

/**
 * Description of UITools
 *
 * @author joemmanuel
 */

require_once(OMEGAUP_ROOT . '/www/api/ApiCaller.php');

class UITools {
    public static $IsLoggedIn = false;
    public static $isAdmin = false;

    /**
     * Set rank by problems solved
     *
     * @param Smarty smarty
     * @param int $offset
     * @param int $rowcount
     */
    public static function setRankByProblemsSolved(Smarty $smarty, $offset, $rowcount) {
        $rankRequest = new Request(array('offset' => $offset, 'rowcount' => $rowcount));
        $response = UserController::getRankByProblemsSolved2($rankRequest);

        $smarty->assign('rank', $response);
    }

    /**
     * If user is not logged in, redirect to login page
     */
    public static function redirectToLoginIfNotLoggedIn() {
        if (self::$IsLoggedIn === false) {
            header('Location: /login.php?redirect=' . $_SERVER['REQUEST_URI']);
            die();
        }
    }

    /**
     * If user is not logged in or isn't an admin, redirect to home page
     */
    public static function redirectIfNoAdmin() {
        if (self::$isAdmin !== true) {
            header('Location: /');
            die();
        }
    }

    /**
     * Set profile in smarty var
     *
     * @param Smarty $smarty
     */
    public static function setProfile(Smarty $smarty) {
        $profileRequest = new Request(array(
                    'username' => array_key_exists('username', $_REQUEST) ? $_REQUEST['username'] : null,
                    'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN')
                ));
        $profileRequest->method = 'UserController::apiProfile';
        $response = ApiCaller::call($profileRequest);

        if ($response['status'] === 'ok') {
            $response['userinfo']['graduation_date'] = is_null($response['userinfo']['graduation_date']) ?
                    null : gmdate('d/m/Y', $response['userinfo']['graduation_date']);

            $response['userinfo']['birth_date'] = is_null($response['userinfo']['birth_date']) ?
                    null : gmdate('d/m/Y', $response['userinfo']['birth_date']);

            $smarty->assign('profile', $response);
        } else {
            $smarty->assign('STATUS_ERROR', $response['error']);
        }
    }
}
