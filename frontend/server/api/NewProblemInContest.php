<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /contests/:id:/problem/new
 * Si el usuario tiene permisos de juez o admin, crea un nuevo problema para el concurso :id
 *
 * */

require_once("ApiHandler.php");

require_once(SERVER_PATH . '/libs/FileHandler.php');
require_once(SERVER_PATH . '/libs/FileUploader.php');
require_once(SERVER_PATH . '/libs/ZipHandler.php');
require_once(SERVER_PATH . '/libs/ProblemContentsZipValidator.php');
require_once(SERVER_PATH . '/libs/Markdown/markdown.php');

class NewProblemInContest extends ApiHandler
{            
    public function NewProblemInContest(FileUploader $fileUploader = NULL)
    {
        // Set file uploader to the file handler
        if(is_null($fileUploader))
        {
            $fileUploader = new FileUploader();            
        }
        
        FileHandler::SetFileUploader($fileUploader);
    }
    
    private $filesToUnzip;
    private $casesFiles;
    
    protected function RegisterValidatorsToRequest()
    {   
       ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");

            
        // Only director is allowed to create problems in contest
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        catch(Exception $e)
        {  
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }                
        
        if(!Authorization::IsContestAdmin($this->_user_id, $contest))
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }
        
                     
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    $u = new Users();
                    $u->setUsername($value);
                    return count(UsersDAO::search($u)) === 1;
                }, "author_username is invalid."))
            ->validate(RequestContext::get("author_username"), "author_username");
                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("title"),
                "title");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("source"),
                "source");
                
        ValidatorFactory::stringOfMaxLengthValidator(32)->validate(
                RequestContext::get("alias"),
                "alias");
        
        ValidatorFactory::numericValidator()->validate(
                    RequestContext::get("public"),
                    "public");   
        
        ValidatorFactory::enumValidator(array("remote", "literal", "token", "token-caseless", "token-numeric"))
                ->validate(RequestContext::get("validator"), "validator");
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("time_limit"), "time_limit");
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("memory_limit"), "memory_limit");                
                
        ValidatorFactory::enumValidator(array("normal", "inverse"))
                ->validate(RequestContext::get("order"), "order"); 
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("points"), "points");
        
        self::ValidateZip($this->filesToUnzip, $this->casesFiles);                
    }       
    
    protected function GenerateResponse() 
    {

		
        // Populate a new Problem object
        $problem = new Problems();
        $problem->setPublic(RequestContext::get("public"));        
        $problem->setTitle(RequestContext::get("title"));
        $problem->setAlias(RequestContext::get("alias"));
        $problem->setValidator(RequestContext::get("validator"));
        $problem->setTimeLimit(RequestContext::get("time_limit"));
        $problem->setMemoryLimit(RequestContext::get("memory_limit"));
        $problem->setVisits(0);
        $problem->setSubmissions(0);
        $problem->setAccepted(0);
        $problem->setDifficulty(0);
        $problem->setSource(RequestContext::get("source"));
        $problem->setOrder(RequestContext::get("order"));                              
        
        // Get user_id from username for author_id        
        $u = new Users();
        $u->setUsername(RequestContext::get("author_username"));        
        try
        {
            $users = UsersDAO::search($u);
        }
        catch(Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );
        }
        
        $problem->setAuthorId($users[0]->getUserId());
        
                
        // Insert new problem
        try
        {
            // Get contest 
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
            
            //Begin transaction
            ProblemsDAO::transBegin();

            // Save the contest object with data sent by user to the database
            ProblemsDAO::save($problem);

            // Save relationship between problems and contest_id
            $relationship = new ContestProblems( array(
                "contest_id" => $contest->getContestId(),
                "problem_id" => $problem->getProblemId(),
                "points"     => RequestContext::get("points")));
            ContestProblemsDAO::save($relationship);                        

            //End transaction
            ProblemsDAO::transEnd();
            
            // Create file after we know that alias is unique
            self::DeployProblemZip($this->filesToUnzip, $this->casesFiles);
        }
        catch(ApiException $e)
        {
            // Operation failed in the data layer, rollback transaction 
            ProblemsDAO::transRollback();
            
            throw $e;
        }
        catch(Exception $e)
        {  
           // Operation failed in the data layer, rollback transaction 
            ProblemsDAO::transRollback();
            
            // Alias may be duplicated, 1062 error indicates that
            if(strpos($e->getMessage(), "1062") !== FALSE)
            {
                throw new ApiException( ApiHttpErrors::duplicatedEntryInDatabase("alias"), $e);    
            }
            else
            {
               throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
            }
        }  
        
        // Adding unzipped files to response
        $this->addResponse("uploaded_files", $this->filesToUnzip);
        
        // All clear
        $this->addResponse("status", "ok");
    }    
    
    public static function ValidateZip(&$filesToUnzip, &$casesFiles)
    {
        if(isset($_FILES['problem_contents']) &&
                !FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name']))
        {
            throw new ApiException(ApiHttpErrors::invalidParameter("problem_contents is missing."));
        }
        
        // Validate zip contents
        $zipValidator = new Validator();
        $zipValidator->addValidator(new ProblemContentsZipValidator)
                     ->validate($_FILES['problem_contents']['tmp_name'], 'problem_contents');                
        
        // Save files to unzip                
        Logger::log("Saving files to unzip...");
        $filesToUnzip = $zipValidator->getValidator(0)->filesToUnzip;        
        $casesFiles = $zipValidator->getValidator(0)->casesFiles;

        sort($casesFiles);
    }
    
    private static function HandleStatements($dirpath, $filesToUnzip)
    {
        // Get a list of all available statements.
        // At this point, zip is validated and it has at least 1 statement. No need to check
        $statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $filesToUnzip);
        Logger::log("Handling statements...");          
        
        // Transform statements from markdown to HTML  
        foreach($statements as $statement)
        {
            // Get the path to the markdown unzipped file
            $markdown_filepath = $dirpath . DIRECTORY_SEPARATOR . $statement;
            Logger::log("Reading file ".$markdown_filepath);
            
            // Read the contents of the original markdown file
            $markdown_file_contents = FileHandler::ReadFile($markdown_filepath);
            
            // Fix for Windows Latin-1 statements:
            // For now, assume that if it is not UTF-8, then it is Windows Latin-1 and then convert
            if (!mb_check_encoding($markdown_file_contents, "UTF-8"))
            {    
                Logger::log("File is not UTF-8.");
                
                // Convert from ISO-8859-1 (Windows Latin1) to UTF-8
                Logger::log("Converting encoding from ISO-8859-1 to UTF-8 (Windows Latin1 to UTF-8, fixing accents)");
                $markdown_file_contents = mb_convert_encoding($markdown_file_contents, "UTF-8", "ISO-8859-1");                
                
                // Then overwrite it into the statement file
                Logger::log("Overwriting file after encoding conversion: ". $markdown_filepath);
                FileHandler::CreateFile($markdown_filepath, $markdown_file_contents);
            }
            else
            {
                Logger::log("File is UTF-8. Nice :)");
            }

            // Transform markdown to HTML
            Logger::log("Transforming markdown 2 html");
            $html_file_contents = markdown($markdown_file_contents); 

            // Get the language of this statement            
            $lang = basename($statement, ".markdown");
            
            $html_filepath = $dirpath . DIRECTORY_SEPARATOR . "statements" . DIRECTORY_SEPARATOR . $lang . ".html";
            
            // Save the HTML file in the path .../problem_alias/statements/lang.html            
            Logger::log("Saving HTML statement in ".$html_filepath );
            FileHandler::CreateFile($html_filepath, $html_file_contents);
        }

    }
    
    
    private static function HandleCases($dirpath, $casesFiles)
    {
        Logger::log("Handling cases...");                
        
        // Aplying dos2unix to cases
        $return_var = 0;
        $dos2unix_cmd = "dos2unix ". $dirpath . DIRECTORY_SEPARATOR . "cases/* 2>&1";
        Logger::log("Applying dos2unix: " . $dos2unix_cmd);                                        
        exec($dos2unix_cmd, $output, $return_var);
        
        // Log errors
        if ($return_var !== 0)
        {
            Logger::warn("dos2unix failed with error: ". $return_var);
        }
        else 
        {
            Logger::log("dos2unix succeeded");
        }
        Logger::log(implode(" | ", $output));        
        
        // After dos2unixfication, we need to generate a zip file that will be
        // passed between grader and runners with the INPUT files...
        // 
        // Instantiate ZipArchive helper object that will handle zip operations.
        $casesZip = new ZipArchive;
        
        // Create path to cases.zip
        $cases_zip_path = $dirpath . DIRECTORY_SEPARATOR . 'cases.zip';
        Logger::log("Zipping input cases into: ". $cases_zip_path);
        
        // Open the zip file
        if (($error = $casesZip->open($cases_zip_path, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) !== TRUE)
        {
            Logger::error($error);
            throw new Exception($error);
        }

        // For each of the cases files detected in the zip
        for ($i = 0; $i < count($casesFiles); $i++)
        {
            // Ignore output cases
            if (substr($casesFiles[$i], -3) !== ".in") 
            {
                continue;
            }
            
            // Get paths to case and generate the actual name into the zip (localname)
            $path_to_case = $dirpath . DIRECTORY_SEPARATOR . $casesFiles[$i];
            $local_name = substr($casesFiles[$i], strlen('cases/'));
            Logger::log("Adding case " . $path_to_case . " to cases.zip with local name " . $local_name);
            
            // Add file to zip
            if (!$casesZip->addFile($path_to_case, $local_name))
            {
                Logger::error("Error trying to add {$casesFiles[$i]} to cases.zip");
                throw new Exception("Error trying to add {$casesFiles[$i]} to cases.zip");
            }
        }
        
        // Close the zip file        
        $casesZip->close();
        
        // Generate sha1sum for checksum validation
        Logger::log("Writing to : " . $dirpath . DIRECTORY_SEPARATOR . "inputname" );
        file_put_contents($dirpath . DIRECTORY_SEPARATOR . "inputname", sha1_file($cases_zip_path));        
    }
    
    public static function DeployProblemZip($filesToUnzip, $casesFiles, $isUpdate = false)
    {
        try 
        {
            // Create paths
            $dirpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . RequestContext::get("alias");
            $filepath = $dirpath . DIRECTORY_SEPARATOR . 'contents.zip';
            
            if ($isUpdate === true)
            {
                // Drop contents into path required
                FileHandler::DeleteDirRecursive($dirpath);                
            }
            
            FileHandler::MakeDir($dirpath);                            
            FileHandler::MoveFileFromRequestTo('problem_contents', $filepath);                                
            ZipHandler::DeflateZip($filepath, $dirpath, $filesToUnzip);

            // Handle statements
            self::HandleStatements($dirpath, $filesToUnzip);
            
            // Handle cases
            self::HandleCases($dirpath, $casesFiles);            
            
        }
        catch (Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidFilesystemOperation("Unable to process problem_contents given. Please check the format. "), $e );
        }
    }
}

?>
