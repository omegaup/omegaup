<?php

require_once("ApiHandler.php");

require_once(SERVER_PATH . '/libs/FileHandler.php');
require_once(SERVER_PATH . '/libs/FileUploader.php');
require_once(SERVER_PATH . '/libs/ZipHandler.php');
require_once(SERVER_PATH . '/libs/ProblemContentsZipValidator.php');
require_once(SERVER_PATH . '/libs/Markdown/markdown.php');

class UpdateProblem extends ApiHandler
{   
    
    private $problem;


    protected function RegisterValidatorsToRequest()
    {   
        // Alias is required to know which contest to edit
        if (RequestContext::get("problem_alias") == null)
        {
            throw new ApiException( ApiHttpErrors::invalidParameter("Problem alias should be specified.") );    
        }
        
        // Get the contest from the DB
        try
        {
            $this->problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {            
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );
        }
        
        if ($this->problem == null)
        {
            throw new ApiException( ApiHttpErrors::invalidParameter("Problem alias specified does not exists.") );
        }              
        
        if(!Authorization::CanEditProblem($this->_user_id, $this->problem))
        {            
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }        
                           
        if (!is_null(RequestContext::get("title")))  
        {
            ValidatorFactory::stringNotEmptyValidator()->validate(
                    RequestContext::get("title"),
                    "title");
        }
        
        if (!is_null(RequestContext::get("source")))
        {
            ValidatorFactory::stringNotEmptyValidator()->validate(
                    RequestContext::get("source"),
                    "source");
        }        
        
        if (!is_null(RequestContext::get("validator")))
        {
            ValidatorFactory::enumValidator(array("remote", "literal", "token", "token-caseless", "token-numeric"))
                    ->validate(RequestContext::get("validator"), "validator");
        }
        
        if (!is_null(RequestContext::get("time_limit")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                    ->validate(RequestContext::get("time_limit"), "time_limit");
        }
        
        if (!is_null(RequestContext::get("memory_limit")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                    ->validate(RequestContext::get("memory_limit"), "memory_limit");                
        }
         
        if (!is_null(RequestContext::get("order")))
        {
            ValidatorFactory::enumValidator(array("normal", "inverse"))
                ->validate(RequestContext::get("order"), "order"); 
        }
        
        if (!is_null(RequestContext::get("points")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                    ->validate(RequestContext::get("points"), "points");
        }
        
        /* @TODO: File update handling
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
        $this->filesToUnzip = $zipValidator->getValidator(0)->filesToUnzip;        
        $this->casesFiles = $zipValidator->getValidator(0)->casesFiles;


        sort($this->casesFiles);
         * 
         */
        
    }       
    
    protected function GenerateResponse() 
    {

		
        // Update the Problem object
        
        if (!is_null(RequestContext::get("public")))
        {
            $this->problem->setPublic(RequestContext::get("public"));
        }
        
        if (!is_null(RequestContext::get("title")))
        {
            $this->problem->setTitle(RequestContext::get("title"));
        }
        
        if (!is_null(RequestContext::get("alias")))
        {
            $this->problem->setAlias(RequestContext::get("alias"));
        }
        
        if (!is_null(RequestContext::get("validator")))
        {
            $this->problem->setValidator(RequestContext::get("validator"));
        }
        
        if (!is_null(RequestContext::get("time_limit")))
        {
            $this->problem->setTimeLimit(RequestContext::get("time_limit"));
        }
        
        if (!is_null(RequestContext::get("memory_limit")))
        {
            $this->problem->setMemoryLimit(RequestContext::get("memory_limit"));
        }

        if (!is_null(RequestContext::get("source")))
        {
            $this->problem->setSource(RequestContext::get("source"));
        }
        
        if (!is_null(RequestContext::get("order")))
        {
            $this->problem->setOrder(RequestContext::get("order"));                              
        }
                      
                
        // Insert new problem
        try
        {
                        
            //Begin transaction
            ProblemsDAO::transBegin();
            
            // Save the contest object with data sent by user to the database
            ProblemsDAO::save($this->problem);
            
            /* @TODO redacción handling, and flag to check which reddación to use
             * @TODO file handling pending
            // Create file after we know that alias is unique
            try 
            {
                // Create paths
                $dirpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . RequestContext::get("alias");
                $filepath = $dirpath . DIRECTORY_SEPARATOR . 'contents.zip';

                // Drop contents into path required
                FileHandler::MakeDir($dirpath);                
                FileHandler::MoveFileFromRequestTo('problem_contents', $filepath);                                
                ZipHandler::DeflateZip($filepath, $dirpath, $this->filesToUnzip);
                
                // Transform statements from markdown to HTML
                $statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $this->filesToUnzip);
                
                foreach($statements as $statement)
                {
                    $filepath = $dirpath . DIRECTORY_SEPARATOR . $statement;
                    $file_contents = FileHandler::ReadFile($filepath);
                    
                    // Markup
                    $file_contents = markdown($file_contents); 
                    
                    // Overwrite file
                    $lang = basename($statement, ".markdown");
                    FileHandler::CreateFile($dirpath . DIRECTORY_SEPARATOR . "statements" . DIRECTORY_SEPARATOR . $lang . ".html", $file_contents);
                }
               
                // Create cases.zip and inputname
                $casesZip = new ZipArchive;
                $casesZipPath = $dirpath . DIRECTORY_SEPARATOR . 'cases.zip';

                if (($error = $casesZip->open($casesZipPath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)) !== TRUE)
                {
					Logger::error($error);
                    throw new Exception($error);
                }

				for ($i = 0; $i < count($this->casesFiles); $i++)
                {
                    if (!$casesZip->addFile($dirpath . DIRECTORY_SEPARATOR . $this->casesFiles[$i], substr($this->casesFiles[$i], strlen('cases/'))))
                    {
						Logger::error("Error trying to add {$this->casesFiles[$i]} to cases.zip");
                        throw new Exception("Error trying to add {$this->casesFiles[$i]} to cases.zip");
                    }
                }

                $casesZip->close();
				Logger::log("Writing to : " . $dirpath . DIRECTORY_SEPARATOR . "inputname" );
                file_put_contents($dirpath . DIRECTORY_SEPARATOR . "inputname", sha1_file($casesZipPath));
            }
            catch (Exception $e)
            {
                throw new ApiException( ApiHttpErrors::invalidFilesystemOperation("Unable to process problem_contents given. Please check the format. "), $e );
            }
             * */             

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
            var_dump($e->getMessage());
           // Operation failed in the data layer, rollback transaction 
            ProblemsDAO::transRollback();
                       
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
        }  
        
        // Adding unzipped files to response
        /* @TODO File handling pending
        $this->addResponse("uploaded_files", $this->filesToUnzip);
         * 
         */
        
        // All clear
        $this->addResponse("status", "ok");
    }  
}
?>
