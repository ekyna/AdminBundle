<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Exception;

use Throwable;
use UnexpectedValueException as BaseException;

use function array_slice;
use function count;
use function get_class;
use function gettype;
use function implode;
use function is_object;
use function reset;
use function sprintf;

/**
 * Class InvalidTypeException
 * @package Ekyna\Bundle\AdminBundle\Show\Exception
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UnexpectedTypeException extends BaseException implements ExceptionInterface
{
    public function __construct(mixed $value, array|string $types, int $code = 0, Throwable $previous = null)
    {
        $types = (array)$types;

        if (1 === $length = count($types)) {
            $types = reset($types);
        } elseif (2 === $length) {
            $types = implode(' or ', $types);
        } else {
            $types = implode(', ', array_slice($types, 0, $length - 2)) . ' or ' . $types[$length - 1];
        }

        $message = sprintf('Expected %s, got %s', $types, is_object($value) ? get_class($value) : gettype($value));

        parent::__construct($message, $code, $previous);
    }
}
