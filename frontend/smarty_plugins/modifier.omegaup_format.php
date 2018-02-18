<?php

function smarty_modifier_omegaup_format($format, $params) {
    return ApiUtils::FormatString($format, $params);
}
