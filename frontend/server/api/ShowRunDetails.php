<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /runs/:id:/details/
 * Si el usuario tiene permisos de juez o admin, obtiene toda la información de la ejecución de un run.
 *
 * */

require_once("ApiHandler.php");
require_once(SERVER_PATH . '/libs/FileHandler.php');
require_once(SERVER_PATH . '/libs/Grader.php');
require_once(SERVER_PATH . '/libs/Cache.php');
require_once("Scoreboard.php");

class ShowRunDetails extends ApiHandler
{     
	private $run;
	private $contest;
	private $problem;

	protected function RegisterValidatorsToRequest()
	{    
		ValidatorFactory::stringNotEmptyValidator()->addValidator(
			new CustomValidator(function ($value)
			{
				// Check if the contest exists
				return RunsDAO::getByAlias($value);
			}, "Run is invalid.")
		)->validate(RequestContext::get("run_alias"), "run_alias");
	    
		try
		{                        
			$this->run = RunsDAO::getByAlias(RequestContext::get("run_alias"));
	    
			$this->contest = ContestsDAO::getByPK($this->run->getContestId());
			$this->problem = ProblemsDAO::getByPK($this->run->getProblemId());
		}
		catch(Exception $e)
		{
			// Operation failed in the data layer
			throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);        
		}                        

		if (!(3 == $this->_user_id ||
                      37 == $this->_user_id || 
   		      $this->contest->getDirectorId() == $this->_user_id ||
		      $this->problem->getAuthorId() == $this->_user_id))
		{
			throw new ApiException(ApiHttpErrors::forbiddenSite());
		}
	}

	protected function ValidateRequest() 
	{                            
	}

	protected function GenerateResponse() 
	{
		$problem_dir = PROBLEMS_PATH . '/' . $this->problem->getAlias() . '/cases/';
		$grade_dir = RUNS_PATH . '/../grade/' . $this->run->getRunId() ;

		$cases = array();

		if (file_exists("$grade_dir.err")) {
			$this->addResponse('compile_error', file_get_contents("$grade_dir.err"));
		} else if (is_dir($grade_dir)) {
			if ($dir = opendir($grade_dir)) {
				while (($file = readdir($dir)) !== false) {
					if ($file == '.' || $file == '..' || !strstr($file, ".meta")) continue;
					
					$case = array('name' => str_replace(".meta", "", $file), 'meta' => $this->ParseMeta(file_get_contents("$grade_dir/$file")));

					if (file_exists("$grade_dir/" . str_replace(".meta", ".out", $file))) {
						$out = str_replace(".meta", ".out", $file);
						$case['out_diff'] = `diff -wui $problem_dir/$out $grade_dir/$out | tail -n +3 | head -n50`;
					}

					array_push($cases, $case);
				}
				closedir($dir);
			}
		}

		usort($cases, array($this, "MetaCompare"));

		$this->addResponse('cases', $cases);
		$this->addResponse('source', file_get_contents(RUNS_PATH . '/' . $this->run->getGuid()));
	}

	private function ParseMeta($meta) {
		$ans = array();

		foreach (explode("\n", trim($meta)) as $line) {
			list($key, $value) = explode(":", trim($line));
			$ans[$key] = $value;
		}

		return $ans;
	}

	private function MetaCompare($a, $b) {
		if ($a['name'] == $b['name']) return 0;

		return ($a['name'] < $b['name']) ? -1 : 1;
	}
}
