<?php

/**
 * Description of UITools
 *
 * @author joemmanuel
 */
class UITools {
	
	/**
	 * Set rank by problems solved
	 * 
	 * @param Smarty smarty
	 * @param int $offset
	 * @param int $rowcount
	 */
	public static function setRankByProblemsSolved(Smarty $smarty, $offset, $rowcount) {
		
		$rankRequest = new Request(array("offset" => $offset, "rowcount" => $rowcount));
		$response = UserController::getRankByProblemsSolved($rankRequest);	
		
		$smarty->assign('rank', $response);
		
	}
}

