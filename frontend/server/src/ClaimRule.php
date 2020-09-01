<?php
declare(strict_types=1);
namespace OmegaUp;

class ClaimRule implements \ParagonIE\Paseto\ValidationRuleInterface {
    /** @var string $failure */
    protected $failure = 'OK';

    /** @var string $rule */
    protected $rule;

    /** @var string $value */
    protected $value;

    /**
     * ClaimRule constructor.
     * @param string $rule
     * @param string $value
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
    public function isValid(\ParagonIE\Paseto\JsonToken $token): bool {
        try {
            $value = $token->get($this->claim);
            if (!\hash_equals($this->value, $value)) {
                $this->failure = 'This token was expected to be for claim "' .
                    $this->claim . '" = "' . $this->value . '"; instead, it ' .
                        'is intended for "' . $claim . '" instead.';
                return false;
            }
        } catch (\ParagonIE\Paseto\PasetoException $ex) {
            $this->failure = $ex->getMessage();
            return false;
        }
        return true;
    }
}
