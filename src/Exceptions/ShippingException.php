<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Exceptions;

use RuntimeException;

/** Base for every error this SDK throws. Catch this to handle all of them. */
class ShippingException extends RuntimeException
{
}
