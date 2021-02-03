<?php

/**
 * @param string $format
 * @param array<string, string> $params
 */
function smarty_modifier_omegaup_format(string $format, array $params): string {
    return \OmegaUp\ApiUtils::formatString($format, $params);
}
