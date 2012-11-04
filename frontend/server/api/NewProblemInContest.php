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
        
        // order_in_contest of the problems in the contest. It's optional
        if (!is_null(RequestContext::get("order_in_contest")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("order_in_contest"), "order_in_contest");
        }
        
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
                "points"     => RequestContext::get("points"),
                "order"      => is_null(RequestContext::get("order_in_contest")) ? 
                                    1 : RequestContext::get("order_in_contest") ));
            
            ContestProblemsDAO::save($relationship);                        
            
            // Create file after we know that alias is unique
            self::DeployProblemZip($this->filesToUnzip, $this->casesFiles);

            //End transaction
            ProblemsDAO::transEnd();                        
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
            Logger::log("Transforming markdown to html");
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
        $output = array();
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
        Logger::log(implode("\n", $output));        
        
        
        // After dos2unixfication, we need to generate a zip file that will be
        // passed between grader and runners with the INPUT files...                
        // Create path to cases.zip and proper cmds
        $cases_zip_path = $dirpath . DIRECTORY_SEPARATOR . 'cases.zip';
        $cases_to_be_zipped = $dirpath . DIRECTORY_SEPARATOR . "cases/*.in";
        
        // cmd to be executed in console
        $zip_cmd = "zip -j ". $cases_zip_path . " " . $cases_to_be_zipped . " 2>&1";
        
        // Execute zip command
        $output = array();
        Logger::log("Zipping input cases using: ". $zip_cmd);                                
        exec($zip_cmd, $output, $return_var);
        
        // Check zip cmd return value
        if ($return_var !== 0)
        {
            // D:
            Logger::error("zipping cases failed with error: ". $return_var);
            throw new Exception("Error creating cases.zip. Please check log for details");
        }
        else 
        {
            // :D
            Logger::log("zipping cases succeeded:");
            Logger::log(implode("\n", $output));  
        }              
        
        // Generate sha1sum for cases.zip distribution from grader to runners
        Logger::log("Writing to : " . $dirpath . DIRECTORY_SEPARATOR . "inputname" );
        file_put_contents($dirpath . DIRECTORY_SEPARATOR . "inputname", sha1_file($cases_zip_path));        
    }
    
    
    
    private static function UpdateContentsDotZip($dirpath, $path_to_contents_zip)
    {        
        
        // Delete whathever the user sent us
        if (!unlink($path_to_contents_zip))
        {
            Logger::warn("Unable to delete contents.zip to replace with original contents!: " . $path_to_contents_zip);            
            return;
        }
        
        // Set directory to the one where contents.zip is to handle paths inside
        // the zip correcly 
        $original_dir = getcwd();
        chdir($dirpath);
        
        // cmd to be executed in console
        // cases/*
        $output = array();
        
        $zip_cmd = "zip -r ". $path_to_contents_zip . " cases/* 2>&1";
        Logger::log("Zipping contents.zip cases using: ". $zip_cmd);
        exec($zip_cmd, $output, $return_var);
        
        // Check zip cmd return value
        if ($return_var !== 0)
        {
            // D:
            Logger::error("zipping cases/* contents.zip failed with error: ". $return_var);            
        }
        else 
        {
            // :D
            Logger::log("zipping cases contents.zip succeeded:");
            Logger::log(implode("\n", $output));  
        }
        
        // 
        // statements/*
        $output = array();
        
        $zip_cmd = "zip -r ". $path_to_contents_zip . " statements/* 2>&1";
        Logger::log("Zipping contents.zip statements using: ". $zip_cmd);
        exec($zip_cmd, $output, $return_var);        
        
        
        // Check zip cmd return value
        if ($return_var !== 0)
        {
            // D:
            Logger::error("zipping statements/* contents.zip failed with error: ". $return_var);            
        }
        else 
        {
            // :D
            Logger::log("zipping statements contents.zip succeeded:");
            Logger::log(implode("\n", $output));  
        }
        
        
        // get back to original dir
        chdir($original_dir);
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
            
            // Making target directory
            FileHandler::MakeDir($dirpath);     
            
            // Move stuff uploaded by user from PHP realm to our directory
            FileHandler::MoveFileFromRequestTo('problem_contents', $filepath);                                
            
            // Unzip the user's zip
            ZipHandler::DeflateZip($filepath, $dirpath, $filesToUnzip);

            // Handle statements
            self::HandleStatements($dirpath, $filesToUnzip);
            
            // Handle cases
            self::HandleCases($dirpath, $casesFiles);     
            
            // Update contents.zip
            self::UpdateContentsDotZip($dirpath, $filepath);
            
        }
        catch (Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidFilesystemOperation("Unable to process problem_contents given. Please check the format. "), $e );
        }
    }
}

?>
