<?php

require_once 'libs/FileHandler.php';
require_once 'libs/FileUploader.php';

/**
 * ProblemParams
 */
class ProblemParams implements ArrayAccess {
    private $params;

    public function __construct($params = null) {
        if (!is_object($params)) {
            $this->params = [];
            if (is_array($params)) {
                $this->params = array_merge([], $params);
            }
        } else {
            $this->params = clone $params;
        }

        ProblemParams::validateParameter('zipName', $this->params, false, OMEGAUP_RESOURCES_ROOT . 'testproblem.zip');
        ProblemParams::validateParameter('title', $this->params, false, Utils::CreateRandomString());
        ProblemParams::validateParameter('visibility', $this->params, false, ProblemController::VISIBILITY_PUBLIC);
        ProblemParams::validateParameter('author', $this->params, false, UserFactory::createUser());
        ProblemParams::validateParameter('languages', $this->params, false, 'c,cpp,py');
    }

    public function offsetGet($offset) {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->params[$offset]);
    }

    /**
     * Checks if array contains a key defined by $parameter
     * @param string $parameter
     * @param array $array
     * @param boolean $required
     * @param $default
     * @return boolean
     * @throws InvalidParameterException
     */
    private static function validateParameter($parameter, &$array, $required = true, $default = null) {
        if (!isset($array[$parameter])) {
            if ($required) {
                throw new InvalidParameterException('ParameterEmpty', $parameter);
            }
            $array[$parameter] = $default;
        }

        return true;
    }
}

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
    public static function getRequest($params = null) {
        if (!($params instanceof ProblemParams)) {
            $params = new ProblemParams($params);
        }

        $r = new Request([
            'title' => $params['title'],
            'problem_alias' => substr(
                preg_replace(
                    '/[^a-zA-Z0-9_-]/',
                    '',
                    str_replace(' ', '-', $params['title'])
                ),
                0,
                32
            ),
            'author_username' => $params['author']->username,
            'validator' => 'token',
            'time_limit' => 5000,
            'overall_wall_time_limit' => 60000,
            'validator_time_limit' => 30000,
            'extra_wall_time' => 0,
            'memory_limit' => 32000,
            'source' => 'yo',
            'order' => 'normal',
            'visibility' => $params['visibility'],
            'output_limit' => 10240,
            'input_limit' => 10240,
            'languages' => $params['languages'],
        ]);

        // Set file upload context
        $_FILES['problem_contents']['tmp_name'] = $params['zipName'];

        return [
            'request' => $r,
            'author' => $params['author'],
            'zip_path' => $params['zipName'],
        ];
    }

    public static function createProblemWithAuthor(Users $author, ScopedLoginToken $login = null) {
        return self::createProblem(new ProblemParams([
            'visibility' => ProblemController::VISIBILITY_PUBLIC,
            'author' => $author,
        ]), $login);
    }

    /**
     *
     */
    public static function createProblem($params = null, ScopedLoginToken $login = null) {
        if (!($params instanceof ProblemParams)) {
            $params = new ProblemParams($params);
        }

        $params['visibility'] = $params['visibility'] >= ProblemController::VISIBILITY_PUBLIC
            ? ProblemController::VISIBILITY_PUBLIC
            : ProblemController::VISIBILITY_PRIVATE;

        // Get a user
        $problemData = self::getRequest($params);
        $r = $problemData['request'];
        $problemAuthorUser = $problemData['author'];
        $problemAuthorIdentity = IdentitiesDAO::getByPK(
            $problemData['author']->main_identity_id
        );

        if ($login == null) {
            // Login user
            $login = OmegaupTestCase::login($problemAuthorUser);
        }
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader(new FileUploaderMock());

        // Call the API
        ProblemController::apiCreate($r);
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        $visibility = $params['visibility'];

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
            'author' => $problemAuthorUser,
            'authorIdentity' => $problemAuthorIdentity,
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
