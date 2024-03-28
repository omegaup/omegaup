<?php

namespace OmegaUp\DAO\Enum;

class ContestOrderStatus extends \OmegaUp\DAO\Enum\StatusBase {
    const NONE = 'none';
    const TITLE = 'title';
    const ENDS = 'ends';
    const DURATION = 'duration';
    const ORGANIZER = 'organizer';
    const CONTESTANTS = 'contestants';
    const SIGNED_UP = 'signedup';
}
