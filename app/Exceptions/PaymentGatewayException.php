<?php

namespace App\Exceptions;

use RuntimeException;

/** The payment gateway could not be reached or returned an error. */
class PaymentGatewayException extends RuntimeException {}
