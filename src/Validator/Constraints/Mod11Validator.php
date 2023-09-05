<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validates whether the value is a valid MOD11.
 * http://www.pgrocer.net/Cis51/mod11.html
 */
class Mod11Validator extends ConstraintValidator
{
    private const MAX_LENGTH = 11;
    private const MIN_LENGTH = 2;

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Mod11) {
            throw new UnexpectedTypeException($constraint, Mod11::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_scalar($value) && !$value instanceof \Stringable) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string)$value;

        $length = \strlen($value);
        $lengthMinus1 = $length - 1;

        if ($length < self::MIN_LENGTH) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Mod11::TOO_SHORT_ERROR)
                ->addViolation();

            return;
        }

        if ($length > self::MAX_LENGTH) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Mod11::TOO_LONG_ERROR)
                ->addViolation();

            return;
        }

        // 1234567890X
        // ^^^^^^^^^^ digits only
        if (!ctype_digit(substr($value, 0, $lengthMinus1))) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Mod11::INVALID_CHARACTERS_ERROR)
                ->addViolation();

            return;
        }

        // 1234567890X
        //           ^ digit or X only for last character.
        $lastCharacter = substr($value, -1);
        if (!ctype_digit(substr($value, -1)) && 'X' !== $lastCharacter) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Mod11::INVALID_CHARACTERS_ERROR)
                ->addViolation();

            return;
        }
        $sumOfDigitsRemainder = $this->validateFindingRemainder($value);

        if (0 !== $sumOfDigitsRemainder) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(Mod11::CHECKSUM_FAILED_ERROR)
                ->addViolation();
        }
    }

    public function validateFindingRemainder(string $value): int
    {
        $length = \strlen($value);
        $lengthMinus1 = $length - 1;

        // Last digit is a checksum, but if it is "X" then it is 10, 0 stands for 11.
        if ('X' === $value[$lengthMinus1]) {
            $sumOfDigits = 10;
        } elseif ('0' == $value[$lengthMinus1]) {
            $sumOfDigits = 11;
        } else {
            $sumOfDigits = (int)$value[$lengthMinus1];
        }

        for ($i = 0; $i < $lengthMinus1; ++$i) {
            // For a value with 11 characters (last being checksum, others numbers),
            // skip last checksum character and multiply the first digit by 11, the second by 9... the 10th digit by 2.
            $newValueToAdd = ($length - $i) * (int)$value[$i];
            $sumOfDigits += $newValueToAdd;
        }

        $sumOfDigitsRemainder = $sumOfDigits % 11;
        return $sumOfDigitsRemainder;
    }
}
