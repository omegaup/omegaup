<?php

namespace OmegaUp;

class ProblemTags {
    public static function addTag(
        string $tagName,
        bool $isPublic,
        \OmegaUp\DAO\VO\Problems $problem,
        bool $allowRestricted = false
    ): void {
        // Normalize name.
        if (!$isPublic) {
            $tagName = \OmegaUp\Controllers\Tag::normalize($tagName);
        }

        if (
            !$allowRestricted &&
            in_array($tagName, \OmegaUp\Controllers\Problem::RESTRICTED_TAG_NAMES)
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'tagRestricted',
                'name'
            );
        }

        $tag = \OmegaUp\DAO\Tags::getByName($tagName);
        if (is_null($tag)) {
            if (in_array($tagName, \OmegaUp\Controllers\Problem::RESTRICTED_TAG_NAMES)) {
                $tag = new \OmegaUp\DAO\VO\Tags([
                    'name' => $tagName,
                    'public' => true,
                ]);
            } else {
                if ($isPublic) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'newPublicTagsNotAllowed',
                        'public'
                    );
                }

                // After normalization problemTag becomes problemtag
                if (str_starts_with($tagName, 'problemtag')) {
                    // Starts with 'problemtag'
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'tagPrefixRestricted',
                        'name'
                    );
                }
                $tag = new \OmegaUp\DAO\VO\Tags([
                    'name' => $tagName,
                    'public' => false,
                ]);
            }
            \OmegaUp\DAO\Tags::create($tag);
        }

        \OmegaUp\DAO\ProblemsTags::replace(new \OmegaUp\DAO\VO\ProblemsTags([
            'problem_id' => $problem->problem_id,
            'tag_id' => $tag->tag_id,
            'source' => 'owner',
        ]));
    }

    public static function removeTag(
        string $tagName,
        \OmegaUp\DAO\VO\Problems $problem
    ): void {
        $tag = \OmegaUp\DAO\Tags::getByName($tagName);
        if (is_null($tag)) {
            throw new \OmegaUp\Exceptions\NotFoundException('tagNotFound');
        }

        if (in_array($tag->name, \OmegaUp\Controllers\Problem::RESTRICTED_TAG_NAMES)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'tagRestricted',
                'name'
            );
        }

        \OmegaUp\DAO\ProblemsTags::delete(new \OmegaUp\DAO\VO\ProblemsTags([
            'problem_id' => $problem->problem_id,
            'tag_id' => $tag->tag_id,
        ]));
    }
}
