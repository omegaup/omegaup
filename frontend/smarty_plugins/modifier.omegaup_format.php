<?php

function smarty_modifier_omegaup_format($format, $params) {
    return \OmegaUp\ApiUtils::formatString($format, $params);
}
