<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Enums;

/** Where the parcel is delivered. Each driver maps these to its own provider codes. */
enum DeliveryType: string
{
    case Home = 'home';      // livraison à domicile
    case StopDesk = 'desk';  // retrait au bureau / stop-desk
}
