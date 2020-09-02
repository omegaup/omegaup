<?php
declare(strict_types=1);
namespace OmegaUp;

class ClaimRule implements \ParagonIE\Paseto\ValidationRuleInterface {
    /** @var string $failure */
    protected $failure = 'OK';

    /** @var string */
    protected $claim;

    /** @var string $value */
    protected $value;

    /**
     * ClaimRule constructor.
     * @param string $claim
     * @param string $value
     */
    public function __construct(string $claim, string $value) {
        $this->claim = $claim;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getFailureMessage(): string {
        return $this->failure;
    }

    /**
     * @param \ParagonIE\Paseto\JsonToken $token
     * @return bool
     */
    public function isValid(\ParagonIE\Paseto\JsonToken $token): bool {
        try {
            /** @var string */
            $value = $token->get($this->claim);
            if (!\hash_equals($this->value, $value)) {
                $this->failure = 'This token was expected to be for claim "' .
                    $this->claim . '" = "' . $this->value . '"; instead, it ' .
                        'is intended for "' . $value . '" instead.';
                return false;
            }
        } catch (\ParagonIE\Paseto\Exception\PasetoException $ex) {
            $this->failure = $ex->getMessage();
            return false;
        }
        return true;
    }
}
