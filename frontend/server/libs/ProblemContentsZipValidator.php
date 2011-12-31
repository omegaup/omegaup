<?php

require_once(SERVER_PATH . '/libs/ZipHandler.php');
require_once(SERVER_PATH . '/libs/FileHandler.php');

require_once('Validator.php');

class ProblemContentsZipValidator extends Validator
{
    public $filesToUnzip;
    public $casesFiles;
    
    public function validate($value)
    {                
        $this->filesToUnzip = array();
        $this->casesFiles = array();
        
        $zip = new ZipArchive();        
        $resource = $zip->open($value);
        
        if($resource === TRUE)
        {            
            // Get list of files
            for($i = 0; $i < $zip->numFiles; $i++)
            {
                $zipFilesArray[] = $zip->getNameIndex($i);
            }

            // Look for testplan
            if(in_array("testplan", $zipFilesArray))
            {                
                $returnValue =  $this->checkCasesWithTestplan($zip, $zipFilesArray);                
                $this->filesToUnzip[] = 'testplan';
            }
            else
            {
                $returnValue = $this->checkCases($zip, $zipFilesArray);
            }            
            
            // Look for statements
            $returnValue = $this->checkProblemStatements($zipFilesArray) && $returnValue;            
            
            // Close zip
            $zip->close();
            
            return $returnValue;
        }
        else
        {
            $this->setError("Unable to open zip." . ZipHandler::zipFileErrMsg($resource));
            return false;
        }                
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
    }
    
    private function checkProblemStatements(array $zipFilesArray)
    {
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
            $this->filesToUnzip[] = $file;
        }
        
        return true;
    }    
}

?>
