<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phramework\Models;

/**
 * Execute class
 *
 * Provides function to execute commands
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class Execute
{
    /**
     * Execute asynchronously using exec
     * @param string $executable Path to executable file
     * @param array [optional] $arguments Executable arguments
     * @param string [optional] $output_stream
     * @uses exec
     * @todo use sprintf, make it more readable
     */
    public static function async($executable, $arguments = [], $output_stream = '/dev/null')
    {
        exec(
            $executable
            . ($arguments ? ' ' . join(' ', $arguments) : '')
            . '>'
            . $output_stream
            . ' 2>&1 &'
        );
    }

    /**
     * Execute using exec
     * @param string $executable Path to executable file
     * @param array $arguments [optional] Executable arguments
     * @param array $output [optional] This array will be filled with every
     * line of output from the command. Trailing whitespace, such as \n, is not
     * @return int the return status of the executed command
     * @uses exec
     * @todo use sprintf, make it more readable
     */
    public static function file($executable, $arguments = [], &$output = false)
    {
        $return_var = 0;
        exec(
            $executable . ($arguments ? ' ' . join(' ', $arguments) : ''),
            $output,
            $return_var
        );

        return $return_var;
    }
}
