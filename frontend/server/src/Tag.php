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
    public $tagname = null;

    /**
     * @readonly
     * @var bool
     */
    public $public = false;

    public function __construct(?string $tagname, bool $isPublic) {
        $this->tagname = $tagname;
        $this->public = $isPublic;
    }
}
