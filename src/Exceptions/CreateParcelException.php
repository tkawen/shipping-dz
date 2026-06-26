<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Exceptions;

/** The carrier rejected (or we couldn't build) a create-parcel request. */
final class CreateParcelException extends ShippingException
{
}
