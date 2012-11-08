<?php

require_once(SERVER_PATH . '/libs/ZipHandler.php');
require_once(SERVER_PATH . '/libs/FileHandler.php');

require_once('Validator.php');

class ProblemContentsZipValidator extends Validator
{
        private $hasValidator = false;
        
	public $filesToUnzip;
	public $casesFiles;

	public function validate($value, $value_name = null)
	{       
		Logger::log("Validating zip...");

		$this->filesToUnzip = array();
		$this->casesFiles = array();

		$zip = new ZipArchive();
		Logger::log("Opening $value...");
		$resource = $zip->open($value);

		$maximumSize = 100 * 1024 * 1024;
		$size = 0;

		if($resource === TRUE)
		{            
			// Get list of files
			for($i = 0; $i < $zip->numFiles; $i++)
			{
				Logger::log("Found inside zip: '".$zip->getNameIndex($i)."'");
				$zipFilesArray[] = $zip->getNameIndex($i);
				$statI = $zip->statIndex($i);
				$size += $statI['size'];

				if (stripos($zip->getNameIndex($i), 'validator.') === 0) {
                                        $this->hasValidator = true;
					$this->filesToUnzip[] = $zip->getNameIndex($i);
					Logger::log("Validator found: " . $zip->getNameIndex($i));
				}
			}

			if ($size > $maximumSize)
			{			
				$this->setError("Extracted zip size ($size) over {$maximumSize}MB. Rejecting.");
				return false;
			}

			// Look for testplan
			if(in_array("testplan", $zipFilesArray))
			{   

				$returnValue =  $this->checkCasesWithTestplan($zip, $zipFilesArray);                
				Logger::log("testplan found, checkCasesWithTestPlan=" . $returnValue );
				$this->filesToUnzip[] = 'testplan';
			}
			else
			{
				Logger::log("testplan not found");	      			
				$returnValue = $this->checkCases($zip, $zipFilesArray);

			}   
                        
                        // Log files to unzip
                        Logger::log("Files to unzip: ");
                        foreach($this->filesToUnzip as $file)
                        {
                            Logger::log($file);
                        }

			// Look for statements
			$returnValue = $this->checkProblemStatements($zipFilesArray, $zip) && $returnValue;            
			Logger::log("checkProblemStatements=". $returnValue . ".");

			// Close zip
			Logger::log("closing zip");
			$zip->close();

			return $returnValue;
		}
		else
		{			
			$this->setError("Unable to open zip." . ZipHandler::zipFileErrMsg($resource));
			return false;
		}

		return true;
	}

	private function checkCasesWithTestplan(ZipArchive $zip, array $zipFilesArray)
	{   
		// Get testplan contents into an array
		$testplan = $zip->getFromName("testplan");                      
		$testplan_array = array();

		// LOL RegEx magic to get test case names from testplan
		preg_match_all('/^\\s*([^#]+?)\\s+(\\d+)\\s*$/m', $testplan, $testplan_array);        

		for($i = 0; $i < count($testplan_array[1]); $i++)
		{                                                
			// Check .in file
			$path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.in';            
			if(!$zip->getFromName($path))
			{				
				$this->setError("Not able to find ". $testplan_array[1][$i] . " in testplan.");                
				return false;
			}                        
			$this->filesToUnzip[] = $path;
			$this->casesFiles[] = $path;

			// Check .out file
			$path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.out';
			if(!$zip->getFromName($path))
			{				
				$this->setError("Not able to find ". $testplan_array[1][$i] . " in testplan.");
				return false;
			}
			$this->filesToUnzip[] = $path;
			$this->casesFiles[] = $path;
		}

		return true;        
	}

	private static function endsWith($haystack, $needle, $case) {
		$expectedPosition = strlen($haystack) - strlen($needle);

		$ans = false;

		if($case)
			return strrpos($haystack, $needle, 0) === $expectedPosition;
		else
			return strripos($haystack, $needle, 0) === $expectedPosition;
	}

	private function checkCases(ZipArchive $zip, array $zipFilesArray)
	{
                // Necesitamos tener al menos 1 input
                $inputs = 0;
                $outputs = 0;
                
		// Add all files in cases/ that end either in .in or .out        
		for ($i = 0; $i < count($zipFilesArray); $i++)
		{            
			$path = $zipFilesArray[$i];                       
			if (strpos($path, "cases/") == 0)                                
			{
                            $isInput = ProblemContentsZipValidator::endsWith($path, ".in", true);
                            $isOutput = ProblemContentsZipValidator::endsWith($path, ".out", true);                                                        
                            
                            if ($isInput || $isOutput)
                            {
                                $this->filesToUnzip[] = $path;
                                $this->casesFiles[] = $path;
                            }
                            
                            if ($isInput){
                                $inputs++;
                            }
                            else if($isOutput){
                                $outputs++;
                            }
                                
			}
		}  
                
                if ($inputs < 1){                    
                    $this->setError("0 inputs found. At least 1 input is needed.");
                    return false;
                }
                
                Logger::log($inputs. " found, " . $outputs . "found ");                
                
                if ($this->hasValidator === false && $inputs != $outputs){                    
                    $this->setError("Inputs/Outputs mistmatch: ". $inputs. " found, " . $outputs . "found ");
                    return false;
                }
                
		return true;
	}

	private function checkProblemStatements(array $zipFilesArray, ZipArchive $zip)
	{
		Logger::log("Checking problem statements...");

		// We need at least one statement
		$statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $zipFilesArray);

		if(count($statements) < 1)
		{			
			$this->setError("No statements found.");
			return false;
		}

		// Add statements to the files to be unzipped
		foreach($statements as $file)
		{
                        // Revisar que los statements no esten vacíos                    
                        if (strlen($zip->getFromName($file, 1)) < 1){
                            $this->setError("Statement {$file} is empty.");
                            return false;
                        }                                
                    
			Logger::log("Adding statements to the files to be unzipped: " . $file);
			$this->filesToUnzip[] = $file;
		}

		return true;
	}    
}

?>
