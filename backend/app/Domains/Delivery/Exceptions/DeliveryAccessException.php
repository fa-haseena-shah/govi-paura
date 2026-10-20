<?php

namespace App\Domains\Delivery\Exceptions;

use RuntimeException;

/** The caller is not allowed to see or change this delivery (controllers answer 403). */
class DeliveryAccessException extends RuntimeException
{
}
