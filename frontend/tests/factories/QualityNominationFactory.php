<?php

/**
 * Description of ProblemsFactory
 *
 * @author heduenas
 */
class QualityNominationFactory {
    public static function createSuggestion($login, $problemAlias, $difficulty, $quality, $tags) {
        $contents = [];
        if ($difficulty != null) {
            $contents['difficulty'] = $difficulty;
        }
        if ($quality != null) {
            $contents['quality'] = $quality;
        }
        if ($tags != null) {
            $contents['tags'] = $tags;
        }
        $contentsJson = json_encode($contents);
        return self::createQualityNomination($login, $problemAlias, 'suggestion', $contentsJson);
    }

    public static function createQualityNomination($login, $problemAlias, $type, $contents) {
        $qualitynomination = QualityNominationController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemAlias,
            'nomination' => $type,
            'contents' => $contents,
        ]));

        return $qualitynomination;
    }
}
