<?php
/**
 * Created by PhpStorm.
 * User: focus
 * Date: 22.08.18
 * Time: 13:02
 */
set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno)
{
    if (error_reporting() == 0) {
        return;
    }
    if (error_reporting() & $severity) {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }
}