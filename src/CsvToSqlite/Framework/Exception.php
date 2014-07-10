<?php

namespace CsvToSqlite\Framework;

/**
 * Define a custom exception class (pretty much straight from php.net)
 */
class Exception extends \Exception {

    protected $message_index = array(
        0   => 'Undefined error.',
        // Configuration exceptions.
        100 => 'An invalid type was passed to the SQLite for configuration. Valid types are "memory" or "file".',
        101 => 'A %s must be provided for SQLite databases of type "%s".',

        // Programming exceptions.
        200 => 'Required value %s not passed to class %s constructor.',
        201 => 'Required value not passed to method %s',

        // System compatability.
        500 => 'Incompatible environment: extension "%s" not loaded.',
    );

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = NULL, array $arguments) {
        $code = (int) $code;
        if (is_null($message) && $code !== 0) {
            $message = vsprintf($this->message_index[$code], $arguments);
        }
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        $message_template  = __CLASS__ . " [{$this->code}]: {$this->message}" . PHP_EOL;
        $message_template .= "In file: " . $this->file . PHP_EOL;
        $message_template .= "At line: " . $this->line . PHP_EOL;
        return $message_template;
    }

}


function exceptionTest()
{
    try {
        throw new Exception(null, 100, null, array('lolly pop', 'baby'));
    }
    catch (Exception $e) {
        echo "Caught TestException ('{$e->getMessage()}')\n{$e}\n";
    }
    catch (Exception $e) {
        echo "Caught Exception ('{$e->getMessage()}')\n{$e}\n";
    }
}

echo '<pre>' . exceptionTest() . '</pre>';
