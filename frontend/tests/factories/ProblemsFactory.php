<?php

/**
 * Problem: PHPUnit does not support is_uploaded_file and move_uploaded_file
 * native functions of PHP to move files around needed for store zip contents
 * in the required places.
 *
 * Solution: We abstracted those PHP native functions in an object FileUploader.
 * We need to create a new FileUploader object that uses our own implementations.
 *
 */
class FileUploaderMock extends FileUploader {
    public function IsUploadedFile($filename) {
        return file_exists($filename);
    }

    public function MoveUploadedFile($filename, $targetPath) {
        $filename = func_get_arg(0);
        $targetpath = func_get_arg(1);

        return copy($filename, $targetpath);
    }
}

/**
 * Description of ProblemsFactory
 *
 * @author joemmanuel
 */
class ProblemsFactory {
    /**
     * Returns a Request object with valid info to create a problem and the
     * author of the problem
     *
     * @param string $title
     * @param string $zipName
     * @return Array
     */
    public static function getRequest($zipName = null, $title = null, $visibility = ProblemController::VISIBILITY_PUBLIC, Users $author = null, $languages = null) {
        if (is_null($author)) {
            $author = UserFactory::createUser();
        }

        if (is_null($title)) {
            $title = Utils::CreateRandomString();
        }

        if (is_null($zipName)) {
            $zipName = OMEGAUP_RESOURCES_ROOT.'testproblem.zip';
        }

        $r = new Request();
        $r['title'] = $title;
        $r['problem_alias'] = substr(preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '-', $r['title'])), 0, 32);
        $r['author_username'] = $author->username;
        $r['validator'] = 'token';
        $r['time_limit'] = 5000;
        $r['overall_wall_time_limit'] = 60000;
        $r['validator_time_limit'] = 30000;
        $r['extra_wall_time'] = 0;
        $r['memory_limit'] = 32000;
        $r['source'] = 'yo';
        $r['order'] = 'normal';
        $r['visibility'] = $visibility;
        $r['output_limit'] = 10240;
        if ($languages == null) {
            $r['languages'] = 'c,cpp,py';
        } else {
            $r['languages'] = $languages;
        }

        // Set file upload context
        $_FILES['problem_contents']['tmp_name'] = $zipName;

        return  [
                'request' => $r,
                'author' => $author,
                'zip_path' => $zipName];
    }

    public static function createProblemWithAuthor(Users $author, ScopedLoginToken $login = null) {
        return self::createProblem(null, null, ProblemController::VISIBILITY_PUBLIC, $author, null, $login);
    }

    /**
     *
     */
    public static function createProblem($zipName = null, $title = null, $visibility = ProblemController::VISIBILITY_PUBLIC, Users $author = null, $languages = null, ScopedLoginToken $login = null) {
        if (is_null($zipName)) {
            $zipName = OMEGAUP_RESOURCES_ROOT.'testproblem.zip';
        }

        // Get a user
        $problemData = self::getRequest(
            $zipName,
            $title,
            ($visibility >= ProblemController::VISIBILITY_PUBLIC)
                ? ProblemController::VISIBILITY_PUBLIC
                : ProblemController::VISIBILITY_PRIVATE,
            $author,
            $languages
        );
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        if ($login == null) {
            // Login user
            $login = OmegaupTestCase::login($problemAuthor);
        }
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader(new FileUploaderMock());

        // Call the API
        ProblemController::apiCreate($r);
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);

        if ($visibility == ProblemController::VISIBILITY_PUBLIC_BANNED
            || $visibility == ProblemController::VISIBILITY_PRIVATE_BANNED
            || $visibility == ProblemController::VISIBILITY_PROMOTED
        ) {
            $problem->visibility = $visibility;
            ProblemsDAO::save($problem);
        }

        // Clean up our mess
        unset($_REQUEST);

        return  [
            'request' => $r,
            'author' => $problemAuthor,
            'problem' => $problem,
        ];
    }

    public static function addAdminUser($problemData, $user) {
        // Prepare our request
        $r = new Request();
        $r['problem_alias'] = $problemData['request']['problem_alias'];
        $r['usernameOrEmail'] = $user->username;

        // Log in the problem author
        $login = OmegaupTestCase::login($problemData['author']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ProblemController::apiAddAdmin($r);

        unset($_REQUEST);
    }

    public static function addGroupAdmin($problemData, Groups $group) {
        // Prepare our request
        $r = new Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'group' => $group->alias,
        ]);

        // Log in the problem author
        $login = OmegaupTestCase::login($problemData['author']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ProblemController::apiAddGroupAdmin($r);
    }

    public static function addTag($problemData, $tag, $public) {
        // Prepare our request
        $r = new Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'name' => $tag,
            'public' => $public
        ]);

        // Log in the problem author
        $login = OmegaupTestCase::login($problemData['author']);
        $r['auth_token'] = $login->auth_token;

        // Call api
        ProblemController::apiAddTag($r);
    }
}
