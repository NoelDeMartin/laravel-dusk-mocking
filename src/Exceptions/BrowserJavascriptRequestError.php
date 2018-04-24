<?php

namespace NoelDeMartin\LaravelDusk\Exceptions;

use Exception;

class BrowserJavascriptRequestError extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param  string|null  $message
     * @return void
     */
    public function __construct($message = null)
    {
        parent::__construct($message);
    }
}
