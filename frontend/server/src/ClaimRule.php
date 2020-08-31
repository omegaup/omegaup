<?php
declare(strict_types=1);
namespace \ParagonIE\Paseto\Rules;

use ParagonIE\Paseto\{
    JsonToken,
    ValidationRuleInterface
};
use ParagonIE\Paseto\Exception\PasetoException;

/**
 * Class ClaimRule
 * @package \ParagonIE\Paseto\Rules
 */
class ClaimRule implements ValidationRuleInterface {
    /** @var string $failure */
    protected $failure = 'OK';

    /** @var string $rule */
    protected $rule;

    /** @var string $value */
    protected $value;

    /**
     * ClaimRule constructor.
     * @param string $audience
     */
    public function __construct(string $rule, string $value) {
        $this->rule = $rule;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getFailureMessage(): string {
        return $this->failure;
    }

    /**
     * @param JsonToken $token
     * @return bool
     */
    public function isValid(JsonToken $token): bool {
        try {
            $claims = $token->getClaims();
            if (!\hash_equals($this->value, $claims[$this->rule])) {
                $this->failure = 'This token is not intended for ' .
                    $this->value . ' (expected); instead, it is intended for ' .
                    $claims[$this->rule] . ' instead.';
                return false;
            }
        } catch (PasetoException $ex) {
            $this->failure = $ex->getMessage();
            return false;
        }
        return true;
    }
}
