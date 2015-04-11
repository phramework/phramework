<?php

namespace Phramework\API\models;

/**
 * Execute class
 * 
 * Provides function to execute commands
 * 
 * @since 0
 * @category models
 * @package API
 */
class execute {
    
    /**
     * Execute asynchronously using exec
     * @param string $executable Path to executable file
     * @param array [optional] $arguments Executable arguments
     * @param string [optional] $output_stream
     */
    public static function execute_async($executable, $arguments = [], $output_stream = '/dev/null') {
        exec($executable . ( $arguments ? ' ' . join(' ', $arguments) : '' ) . '>' . $output_stream . ' 2>&1 &');
    }
    
    /**
     * Execute using exec
     * @param string $executable Path to executable file
     * @param array $arguments [optional] Executable arguments
     * @param array $output [optional] This array will be filled with every line of output from the command. Trailing whitespace, such as \n, is not
     * @return intigerthen the return status of the executed command
     */
    public static function execute_file($executable, $arguments = [], &$output = false) {
        $return_var;
        exec($executable . ( $arguments ? ' ' . join(' ', $arguments) : '' ), $output, $return_var);

        return $return_var;
    }

    /*
      if (substr(php_uname(), 0, 7) == "Windows") {
      pclose(popen("start /B ". $cmd, "r"));
      }
     */
}