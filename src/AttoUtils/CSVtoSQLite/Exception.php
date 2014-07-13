<?php

namespace AttoUtils\CSVtoSQLite;

require dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * Define a custom exception class (pretty much straight from php.net)
 */
class Exception extends \Exception {

    private $message_index = array(
        0   => 'Undefined error.',

        // Configuration exceptions.
        100 => 'An invalid "type" was passed for SQLite configuration. Valid types are "memory" or "file".',
        101 => 'A %s must be provided for SQLite databases of type "%s".',
        102 => 'File(s) for processing not provided for CSVtoSQLite Controller configuration.',
        103 => 'A database configuration was not supplied.',

        // Programming exceptions.
        200 => 'Required value %s not passed to class %s constructor.',
        201 => 'Required value not passed to method %s',

        // System compatability.
        500 => 'Incompatible environment: extension "%s" not loaded.',
        501 => 'File "%s" is not readeable by user %s.',
        502 => 'Configured file directory (%s) is not writable by user %s.',
    );

    // Redefine the exception to be code based so a logical index of error
    // messages can be defined.
    public function __construct($code = 0, array $arguments, string $message = NULL, Exception $previous = NULL) {
        $code = (integer) $code;
        $message = vsprintf($this->message_index[$code], $arguments);
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        $message_template  = "%s [{$this->code}]: {$this->message}" . PHP_EOL;
        $message_template .= "In file: %s" . PHP_EOL;
        $message_template .= "At line: %s" . PHP_EOL;
        $message_template .= "Trace: %s" . PHP_EOL;
        return sprintf($message_template, __CLASS__, $this->file, $this->line, $this->getTraceAsString());
    }

}


// function exceptionTest()
// {
//     try {
//         throw new Exception(100, array('lolly pop', 'baby'));
//     }
//     catch (Exception $e) {
//         echo "Caught TestException ('{$e->getMessage()}')\n{$e}\n";
//     }
//     catch (Exception $e) {
//         echo "Caught Exception ('{$e->getMessage()}')\n{$e}\n";
//     }
// }

// echo '<pre>' . exceptionTest() . '</pre>';
