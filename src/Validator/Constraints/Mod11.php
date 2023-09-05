<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Mod11 extends Constraint
{
    public const TOO_SHORT_ERROR = 'TOO_SHORT_ERROR';
    public const TOO_LONG_ERROR = 'TOO_LONG_ERROR';
    public const INVALID_CHARACTERS_ERROR = 'INVALID_CHARACTERS_ERROR';
    public const CHECKSUM_FAILED_ERROR = 'CHECKSUM_FAILED_ERROR';

    public $message = 'This value is not a valid MOD11.';

    public function __construct(
        array $options = null,
        string $message = null,
        array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
