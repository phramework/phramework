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

use \Phramework\Phramework;
use \Phramework\Exceptions\PermissionException;
use \Phramework\Exceptions\MissingParametersException;
use \Phramework\Exceptions\IncorrectParametersException;

/**
 * Utility class
 *
 * Provides a set of methods that perform common, often re-used functions.
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0
 * @todo add defined settings
 */
class Util
{
    /**
     * Get url of the API resource.
     *
     * This method uses `api_base` setting to create the url.
     * @param string $endpoint [Optional]
     * @param string $suffix [Optional] Will append to the end of url
     * @return string Returns the created url
     */
    public static function url($endpoint = null, $suffix = '')
    {
        $base = Phramework::getSetting('base');

        if ($endpoint) {
            $suffix = $endpoint . '/' . $suffix;

            $suffix = str_replace('//', '/', $suffix);
        }
        return $base . $suffix;
    }

    /**
     * Clears all non ASCII characters from a string and replaces /,_,|,+, ,- characters to '-'
     * @param string $str The input string
     * @return string Returns the clean string
     */
    public static function toAscii($str)
    {
        $clean = preg_replace('/[^a-zA-Z0-9\.\/_|+ -]/', '', $str);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace('/[\/_|+ -]+/', '-', $clean);

        return $clean;
    }

    /**
     * Check if the string begins with the search
     * @param string $string The string that used check
     * @param string $search The string to search
     * @return boolean Returns true if the string begins with the search
     */
    public static function beginsWith($string, $search)
    {
        return (substr($string, 0, strlen($search)) == $search);
    }

    // @codingStandardsIgnoreStart
    /**
     * Print formatted date
     */
    public static function _dateFormatted($datetime, $format = 'j M Y G:i')
    {
        $date = new DateTime($datetime);
        echo $date->format($format);
    }
    // @codingStandardsIgnoreEnd

    public static function dateFormatted($datetime, $format = 'j M Y G:i')
    {
        $date = new DateTime($datetime);
        return $date->format($format);
    }

    /**
     * @uses htmlentities
     * @param string $content
     * @return string
     */
    public static function userContent($content)
    {
        return htmlentities($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a random 40 character hex token
     * @return string Returns a random token
     */
    public static function token($prefix = '')
    {
        $token = sha1(uniqid($prefix, true) . rand());

        return $token;
    }

    /**
     * Check if incoming request is send using ajax
     * @return boolean
     */
    public static function isAjaxRequest()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    /**
     * Check if incoming request is send using HTTPS protocol
     * @return boolean
     */
    public static function isHTTPS()
    {
        return (isset($_SERVER['HTTPS'])
                && ($_SERVER['HTTPS'] == 'on'))
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            );
    }

    /**
     * Get the ip address of the client
     * @return string|false Returns fails on failure
     */
    public static function getIPAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            ///to check ip is pass from proxy
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }

        return false;
    }

    /**
     * Get an array that represents directory tree
     * @param string  $directory     Directory path
     * @param boolean $recursive     *[Optional]* Include sub directories
     * @param boolean $listDirs      *[Optional]* Include directories on listing
     * @param boolean $listFiles     *[Optional]* Include files on listing
     * @param string  $exclude       *[Optional]* Exclude paths that matches this
     * regular expression
     * @param array   $allowed_filetypes *[Optional]* Allowed file extensions,
     * default `[]`` (allow all)
     * @param boolean $relative_path *[Optional]* Return paths in relative form,
     * default `false`
     */
    public static function directoryToArray(
        $directory,
        $recursive = false,
        $listDirs = false,
        $listFiles = true,
        $exclude = '',
        $allowed_filetypes = [],
        $relative_path = false
    ) {
        $arrayItems = [];
        $skipByExclude = false;
        $handle = opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                preg_match(
                    '/(^(([\.]) {1,2})$|(\.(svn|git|md|htaccess))|(Thumbs\.db|\.DS_STORE|\.|\.\.))$/iu',
                    $file,
                    $skip
                );

                if ($exclude) {
                    preg_match($exclude, $file, $skipByExclude);
                }

                if ($allowed_filetypes && !is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                    $ext = strtolower(preg_replace('/^.*\.([^.]+)$/D', '$1', $file));
                    if (!in_array($ext, $allowed_filetypes)) {
                        $skip = true;
                    }
                }
                if (!$skip && !$skipByExclude) {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                        if ($recursive) {
                            $arrayItems = array_merge(
                                $arrayItems,
                                self::directoryToArray(
                                    $directory . DIRECTORY_SEPARATOR . $file,
                                    $recursive,
                                    $listDirs,
                                    $listFiles,
                                    $exclude,
                                    $allowed_filetypes,
                                    $relative_path
                                )
                            );
                        }
                        if ($listDirs) {
                            $arrayItems[] = (
                                $relative_path
                                ? $file
                                : $directory . DIRECTORY_SEPARATOR . $file
                            );
                        }
                    } else {
                        if ($listFiles) {
                            $arrayItems[] = (
                                $relative_path
                                ? $file
                                : $directory . DIRECTORY_SEPARATOR . $file
                            );
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $arrayItems;
    }

    /**
     * Delete all contents from a directory
     * @param string $directory Directory path
     * @param boolean $DELETE_DIRECTORY *[Optional]*, if is set directory will be deleted too.
     */
    public static function deleteDirectoryContents($directory, $DELETE_DIRECTORY = false)
    {
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            $path = Util::get_path([$directory, $file]);
            (
                is_dir($path)
                ? self::delete_directory_contents($path, true)
                : unlink($path)
            );
        }

        return $DELETE_DIRECTORY ? rmdir($directory) : true;
    }

    /**
     * Create a random readable word
     * @param  integer $length *[Optional]* String's length
     * @return string
     */
    public static function readableRandomString($length = 8)
    {
        $conso = [
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
        ];
        $vocal = ['a', 'e', 'i', 'o', 'u'];

        $word = '';
        srand((double) microtime() * 1000000);
        $max = $length / 2;

        for ($i = 1; $i <= $max; ++$i) {
            $word .= $conso[rand(0, 19)];
            $word .= $vocal[rand(0, 4)];
        }
        return $word;
    }

    /**
     * Get Headers from a remote link
     * @param string $url
     * @return array Return headers
     */
    public static function curlHeaders($url, &$data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        $data = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        return $headers;
    }

    /**
     * Download a file from a remote link
     * @param string $url
     * @param string $path
     * @return bool True if download succeed
     */
    public static function curlDownload($url, $path, $timeout = 3600)
    {
        $return = false;
        try {
            // open file to write
            $fp = fopen($path, 'w+');
            // start curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            // set return transfer to false
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // increase timeout to download big file
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            // write data to local file
            curl_setopt($ch, CURLOPT_FILE, $fp);
            // execute curl
            $return = curl_exec($ch);
            // close curl
            curl_close($ch);
            // close local file
            fclose($fp);
        } catch (\Exception $e) {
            return false;
        }
        return $return;
    }

    /**
     * Create a temporary file path
     * @param string $prefix Prefix of the filename
     * @return string The path of the temporary filename
     */
    public static function tempfile($prefix)
    {
        global $settings;
        $folder = '/tmp';
        if (isset($settings['temporary_folder'])) {
            $folder = $settings['temporary_folder'];
        }
        return tempnam($folder, $prefix);
    }

    /**
     * Extract extension from file's path
     * @param string $filePath The file path
     * @return string The extension without dot prefix
     */
    public static function extension($filePath)
    {
        return strtolower(preg_replace('/^.*\.([^.]+)$/D', '$1', $filePath));
    }

    /**
     * Join directories and filename to create path
     * @param array $array Array with directories and filename for example array( '/tmp', 'me', 'file.tmp' )
     * @param string $glue *[Optional]*
     * @return string Path
     */
    public static function getPath($array, $glue = DIRECTORY_SEPARATOR)
    {
        return str_replace('\\\\', '\\', join($glue, $array));
    }

    public static function toSingleSlashes($input)
    {
        return preg_replace('~(^|[^:])//+~', '\\1/', $input);
    }

    public static function parseRegexp($input, $pattern)
    {
        preg_match($pattern, $input, $matches);
        return $matches;
    }

    /**
     * Get the size of a file
     * Works for large files too (>2GB)
     * @param string $path File's path
     * @param double File's size
     */
    public static function getFileSize($path)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            if (class_exists('COM')) {
                $fsobj = new COM('Scripting.FileSystemObject');
                $f = $fsobj->GetFile(realpath($path));
                $size = $f->Size;
            } else {
                $size = trim(exec("for %F in (\"" . $path . "\") do @echo %~zF"));
            }
        } elseif (PHP_OS == 'Darwin') {
            $size = trim(shell_exec("stat -f %z " . escapeshellarg($path)));
        } elseif (in_array(PHP_OS, [ 'Linux', 'FreeBSD', 'Unix', 'SunOS'])) {
            $size = trim(shell_exec("stat -c%s " . escapeshellarg($path)));
        } else {
            $size = filesize($path);
        }

        return doubleval($size);
    }

    /**
     * Generate UUID
     * @return string Returns a 36 characters string
     * @since 1.2.0
     */
    public static function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
