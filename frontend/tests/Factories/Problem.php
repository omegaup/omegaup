<?php

namespace OmegaUp\Test\Factories;

class ProblemParams {
    /**
     * @readonly
     * @var string
     */
    public $zipName;

    /**
     * @readonly
     * @var string
     */
    public $title;

    /**
     * @readonly
     * @var string
     */
    public $alias;

    /**
     * @var 'deleted'|'private_banned'|'public_banned'|'private_warning'|'private'|'public_warning'|'public'|'promoted'
     */
    public $visibility;

    /**
     * @readonly
     * @var string
     */
    public $languages;

    /**
     * @readonly
     * @var \OmegaUp\DAO\VO\Identities
     */
    public $author;

    /**
     * @readonly
     * @var \OmegaUp\DAO\VO\Users
     */
    public $authorUser;

    /**
     * @readonly
     * @var string
     */
    public $showDiff;

    /**
     * @readonly
     * @var bool
     */
    public $allowUserAddTags;

    /**
     * @readonly
     * @var bool
     */
    public $qualitySeal;

    /**
     * @readonly
     * @var string
     */
    public $problemLevel;

    /**
     * @readonly
     * @var string
     */
    public $selectedTags;

    /**
     * @readonly
     * @var string
     */
    public $validator;

    /**
     * @readonly
     * @var float|null
     */
    public $difficulty;

    /**
     * @param array{alias?: string, allow_user_add_tags?: bool, quality_seal?: bool, zipName?: string, title?: string, visibility?: ('deleted'|'private_banned'|'public_banned'|'private_warning'|'private'|'public_warning'|'public'|'promoted'), author?: \OmegaUp\DAO\VO\Identities, authorUser?: \OmegaUp\DAO\VO\Users, languages?: string, show_diff?: string, problem_level?: string, selected_tags?: string, validator?: string, difficulty?: float} $params
     */
    public function __construct($params = []) {
        $this->zipName = $params['zipName'] ?? (OMEGAUP_TEST_RESOURCES_ROOT . 'testproblem.zip');
        $this->title = $params['title'] ?? \OmegaUp\Test\Utils::createRandomString();
        $this->languages = $params['languages'] ?? 'c11-gcc,c11-clang,cpp17-gcc,cpp17-clang,py2,py3';
        $this->visibility = $params['visibility'] ?? 'public';
        $this->showDiff = $params['show_diff'] ?? 'none';
        $this->allowUserAddTags = $params['allow_user_add_tags'] ?? false;
        $this->problemLevel = $params['problem_level'] ?? 'problemLevelBasicIntroductionToProgramming';
        $this->qualitySeal = $params['quality_seal'] ?? false;
        $this->selectedTags = $params['selected_tags'] ?? json_encode([
            [
                'tagname' => 'problemLevelBasicIntroductionToProgramming',
                'public' => true,
            ],
        ]);
        $this->validator = $params['validator'] ?? 'token';
        $this->difficulty = $params['difficulty'] ?? null;

        $problemAlias = substr(
            preg_replace(
                '/[^a-zA-Z0-9_-]/',
                '',
                str_replace(' ', '-', $this->title)
            ),
            0,
            \OmegaUp\Validators::ALIAS_MAX_LENGTH
        );
        $this->alias = $params['alias'] ?? $problemAlias;
        $author = $params['author'] ?? null;
        $authorUser = $params['authorUser'] ?? null;
        if (empty($author) || empty($authorUser)) {
            [
                'user' => $user,
                'identity' => $identity,
            ] = \OmegaUp\Test\Factories\User::createUser();
            $author = $author ?? $identity;
            $authorUser = $authorUser ?? $user;
        }
        $this->author = $author;
        $this->authorUser = $authorUser;
    }
}

/**
 * Problem: PHPUnit does not support is_uploaded_file and move_uploaded_file
 * native functions of PHP to move files around needed for store zip contents
 * in the required places.
 *
 * Solution: We abstracted those PHP native functions in an object FileUploader.
 * We need to create a new FileUploader object that uses our own implementations.
 */
class FileUploaderMock extends \OmegaUp\FileUploader {
    public function isUploadedFile(string $filename): bool {
        return file_exists($filename);
    }

    public function moveUploadedFile(
        string $filename,
        string $targetPath
    ): bool {
        return copy($filename, $targetPath);
    }
}

class Problem {
    /**
     * Returns a Request object with valid info to create a problem and the
     * author of the problem
     *
     * @return array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, request: \OmegaUp\Request, zip_path: string}
     */
    public static function getRequest(?\OmegaUp\Test\Factories\ProblemParams $params = null) {
        if (is_null($params)) {
            $params = new \OmegaUp\Test\Factories\ProblemParams();
        }
        $r = new \OmegaUp\Request([
            'title' => $params->title,
            'problem_alias' => $params->alias,
            'author_username' => $params->author->username,
            'validator' => $params->validator,
            'time_limit' => 5000.0,
            'overall_wall_time_limit' => 60000.0,
            'validator_time_limit' => 30000,
            'extra_wall_time' => 0.0,
            'memory_limit' => 32000,
            'source' => 'yo',
            'order' => 'normal',
            'visibility' => $params->visibility,
            'output_limit' => 10240,
            'input_limit' => 10240,
            'languages' => $params->languages,
            'show_diff' => $params->showDiff,
            'allow_user_add_tags' => $params->allowUserAddTags,
            'quality_seal' => $params->qualitySeal,
            'problem_level' => $params->problemLevel,
            'selected_tags' => $params->selectedTags,
        ]);

        // Set file upload context
        /** @var array<string, array{tmp_name: string}> $_FILES */
        $_FILES['problem_contents']['tmp_name'] = $params->zipName;

        return [
            'request' => $r,
            'author' => $params->author,
            'authorUser' => $params->authorUser,
            'zip_path' => $params->zipName,
        ];
    }

    /**
     * @return array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request}
     */
    public static function createProblemWithAuthor(
        \OmegaUp\DAO\VO\Identities $author,
        \OmegaUp\Test\ScopedLoginToken $login = null
    ): array {
        return self::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'quality_seal' => true,
            'author' => $author,
        ]), $login);
    }

    /**
     * @return array{author: \OmegaUp\DAO\VO\Identities, authorUser: \OmegaUp\DAO\VO\Users, problem: \OmegaUp\DAO\VO\Problems, request: \OmegaUp\Request}
     */
    public static function createProblem(
        ?\OmegaUp\Test\Factories\ProblemParams $params = null,
        \OmegaUp\Test\ScopedLoginToken $login = null
    ): array {
        if (is_null($params)) {
            $params = new \OmegaUp\Test\Factories\ProblemParams();
        }

        $visibility = $params->visibility;

        if ($params->visibility != 'private' && $params->visibility != 'public') {
            $params->visibility = 'public';
        }

        // Get a user
        $problemData = self::getRequest($params);
        $r = $problemData['request'];
        $problemAuthorIdentity = $problemData['author'];

        if (is_null($login)) {
            // Login user
            $login = \OmegaUp\Test\ControllerTestCase::login(
                $problemAuthorIdentity
            );
        }
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting(new FileUploaderMock());

        // Call the API
        \OmegaUp\Controllers\Problem::apiCreate($r);
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $r->ensureString('problem_alias')
        );
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemNotFound'
            );
        }

        if (
            $visibility === 'public_banned'
            || $visibility === 'private_banned'
            || $visibility === 'public_warning'
            || $visibility === 'private_warning'
            || $visibility === 'promoted'
        ) {
            switch (strval($visibility)) {
                case 'private_banned':
                    $problem->visibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED;
                    break;
                case 'public_banned':
                    $problem->visibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED;
                    break;
                case 'private_warning':
                    $problem->visibility = \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_WARNING;
                    break;
                case 'public_warning':
                    $problem->visibility = \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_WARNING;
                    break;
                case 'promoted':
                    $problem->visibility = \OmegaUp\ProblemParams::VISIBILITY_PROMOTED;
                    break;
            }
            \OmegaUp\DAO\Problems::update($problem);
        }
        if ($params->qualitySeal) {
            $problem->quality_seal = true;
            \OmegaUp\DAO\Problems::update($problem);
        }
        if (!is_null($params->difficulty)) {
            $problem->difficulty = $params->difficulty;
            \OmegaUp\DAO\Problems::update($problem);
        }

        return  [
            'request' => $r,
            'author' => $problemAuthorIdentity,
            'authorUser' => $problemData['authorUser'],
            'problem' => $problem,
        ];
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     */
    public static function addAdminUser(
        $problemData,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $problemData['author']
        );
        \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'usernameOrEmail' => $identity->username,
            'auth_token' => $login->auth_token,
        ]));
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     */
    public static function addGroupAdmin(
        $problemData,
        \OmegaUp\DAO\VO\Groups $group
    ): void {
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $problemData['author']
        );
        \OmegaUp\Controllers\Problem::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
            'group' => $group->alias,
        ]));
    }

    /**
     * @param array{problem: \OmegaUp\DAO\VO\Problems, author: \OmegaUp\DAO\VO\Identities, request: \OmegaUp\Request, authorUser: \OmegaUp\DAO\VO\Users} $problemData
     */
    public static function addTag(
        $problemData,
        string $tag,
        int $public
    ): void {
        // Prepare our request
        $r = new \OmegaUp\Request([
            'problem_alias' => $problemData['request']['problem_alias'],
            'name' => $tag,
            'public' => $public
        ]);

        // Log in the problem author
        $login = \OmegaUp\Test\ControllerTestCase::login(
            $problemData['author']
        );
        $r['auth_token'] = $login->auth_token;

        // Call api
        \OmegaUp\Controllers\Problem::apiAddTag($r);
    }
}
