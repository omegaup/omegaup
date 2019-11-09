<?php

namespace OmegaUp;

/**
 *
 * @author juan.pablo
 */
class Tag {
    /**
     * @readonly
     * @var string|null
     */
    public static $tagname = null;

    /**
     * @readonly
     * @var bool
     */
    public static $public = false;

    public function __construct(string $tagname, bool $isPublic) {
        $this->tagname = $tagname;
        $this->public = $isPublic;
    }
}
