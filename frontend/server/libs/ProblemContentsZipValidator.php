<?php

require_once(SERVER_PATH . '/libs/ZipHandler.php');
require_once(SERVER_PATH . '/libs/FileHandler.php');

require_once('Validator.php');

class ProblemContentsZipValidator extends Validator
{
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

		$maximumSize = 256 * 1024 * 1024;
		$size = 0;
        
        if($resource === TRUE)
        {            
            // Get list of files
            for($i = 0; $i < $zip->numFiles; $i++)
            {
				Logger::log("Found ".$zip->getNameIndex($i));
                $zipFilesArray[] = $zip->getNameIndex($i);
				$statI = $zip->statIndex($i);
                $size += $statI['size'];
	    	}

		    if ($size > $maximumSize)
		    {
					Logger::error("Extracted zip size ($size) over {$maximumSize}MB. Rejecting.");
	                $this->setError("Extracted zip over 256MB. Rejecting.");
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
            
            // Look for statements
			
            $returnValue = $this->checkProblemStatements($zipFilesArray) && $returnValue;            
            Logger::log("checkProblemStatements=". $returnValue . ".");


            // Close zip
			Logger::log("closing zip");
            $zip->close();
            
            return $returnValue;
        }
        else
        {
			Logger::error("Unable to open zip." . ZipHandler::zipFileErrMsg($resource));
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
        preg_match_all('/^\\s*([^#]+?)\\s+(\\d+)\\s*$/m', $testplan, &$testplan_array);        
        
        for($i = 0; $i < count($testplan_array[1]); $i++)
        {                                                
            // Check .in file
            $path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.in';            
            if(!$zip->getFromName($path))
            {
				Logger::error("Not able to find ". $testplan_array[1][$i] . " in testplan.");
                $this->setError("Not able to find ". $testplan_array[1][$i] . " in testplan.");                
                return false;
            }                        
            $this->filesToUnzip[] = $path;
            $this->casesFiles[] = $path;
            
            // Check .out file
            $path = 'cases' . DIRECTORY_SEPARATOR . $testplan_array[1][$i] . '.out';
            if(!$zip->getFromName($path))
            {
				Logger::error("Not able to find ". $testplan_array[1][$i] . " in testplan.");
                $this->setError("Not able to find ". $testplan_array[1][$i] . " in testplan.");
                return false;
            }
            $this->filesToUnzip[] = $path;
            $this->casesFiles[] = $path;
        }
        
        return true;        
    }

    private function checkCases(ZipArchive $zip, array $zipFilesArray)
    {
        // Add all files in cases/ that end either in .in or .out
        for ($i = 0; $i < count($zipFilesArray); $i++)
        {
            $path = $zipFilesArray[$i];
            $l = strlen($path);
            if (strpos($path, "cases/" == 0) && (strpos($path, ".in") == $l - 3 || strpos($path, ".out") == $l - 4))
            {
                $this->filesToUnzip[] = $path;
                $this->casesFiles[] = $path;
            }
        }
		return true;
    }
    
    private function checkProblemStatements(array $zipFilesArray)
    {
		Logger::log("Checking problem statements...");
		
        // We need at least one statement
        $statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $zipFilesArray);
                
        if(count($statements) < 1)
        {
			Logger::log("No statements found.");
            $this->setError("No statements found.");
            return false;
        }
        
        // Add statements to the files to be unzipped
        foreach($statements as $file)
        {
			Logger::log("Add statements to the files to be unzipped: " . $file);
            $this->filesToUnzip[] = $file;
        }
        
        return true;
    }    
}

?>
