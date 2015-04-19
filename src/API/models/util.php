<?php

namespace Phramework\API\models;

use Phramework\API\exceptions\permission;
use Phramework\API\exceptions\missing_paramenters;
use Phramework\API\exceptions\incorrect_paramenters;


/**
 * Utility class
 * 
 * Provides a set of methods that perform common, often re-used functions.
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
 */
class util {

    /**
     * Get base url of the API
     * @param type $controller
     * @param string $suffix
     * @return string
     */
    public static function url($controller = NULL, $suffix = '') {
        $api_base = \Phramework\API\API::get_setting('api_base');

        if ($controller) {
            $suffix = $controller . '/' . $suffix;
        }
        return $api_base . $suffix;
    }

    /**
     * Get base url of main web interface application tha uses the API
     * @todo fix
     * @param type $controller
     * @param string $suffix
     * @return string
     */
    public static function url_interface($suffix = '') {
        return get_setting('interface_base') . $suffix;
    }

    /**
     * Clears all non ASCII characters from a string and replaces /,_,|,+, ,- charaters to '-' 
     * @param string $str The input string
     * @return string Returns the clean string 
     */
    public static function to_ascii($str) {
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
    public static function begins_with($string, $search) {
        return ( substr($string, 0, strlen($search)) == $search );
    }
    
    public static function _date_formatted($datetime, $format = 'j M Y G:i') {
        $date = new DateTime($datetime);
        echo $date->format($format);
    }

    public static function date_formatted($datetime, $format = 'j M Y G:i') {
        $date = new DateTime($datetime);
        return $date->format($format);
    }
    
    /**
     * Applies htmlentities
     * @param string $content
     * @return string
     */
    public static function user_content($content) {
        return htmlentities($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a random 40 character hex token
     * @return string Returns a random token
     */
    public static function token($prefix = '') {
        $token = sha1(uniqid($prefix, TRUE) . rand());

        return $token;
    }

    /**
     * Check if incoming request is send using ajax
     * @return boolean
     */
    public static function is_ajax_request() {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Check if incoming request is send using HTTPS protocol
     * @return boolean
     */
    public static function is_HTTPS() {
        return (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') );
    }

    /**
     * Get the ip address of the client
     * @return string
     */
    public static function get_ipaddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {///to check ip is pass from proxy
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
        return FALSE;
    }

    /**
     * Write cache headers
     */
    public static function cache_headers($expires = '+1 hour') {
        if (!headers_sent()) {
            header('Cache-Control: private, max-age=3600');
            header('Pragma: public');
            header('Last-Modified: ' . date(DATE_RFC822, strtotime('-1 second')));
            header('Expires: ' . date(DATE_RFC822, strtotime($expires)));
        }
    }
    
    /**
     * @deprecated since version 0
     */
    public static function check_include_root() {
        //$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 1);DELETE ONLY EXECUTE ??
        //var_dump( array_shift( $trace ) );
        $files = get_included_files();
        if (!in_array(array_shift($files), [ util::get_path([ dirname(__DIR__), 'index.php'])])) {
            die('Unauthorized access');
        }
        unset($files);
    }

    /**
     * Merge put paramters into $parameters array
     * @param array $parameters Parameter's array
     */
    public static function merge_put_paramters(&$parameters) {
        $put_parameters = json_decode(file_get_contents('php://input'), true);
        //Get params
        if (isset($put_params['params'])) {
            $parameters = array_merge($parameters, $put_parameters['params']);
        }
    }

    /**
     * Get an array that represents  directory tree
     * @param string $directory      Directory path
     * @param boolean $recursive        Include sub directories
     * @param boolean $listDirs         Include directories on listing
     * @param boolean $listFiles        Include files on listing
     * @param sstring $exclude [optional]       Exclude paths that matches this regex
     * @param array $allowed_filetypes Allowed file extensions. Optional. Default allow all
     * @param boolean $relative_path    Return paths in relative form. Optional. Default FALSE
     */
    public static function directory_to_array($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '', $allowed_filetypes = [], $relative_path = FALSE) {
        $arrayItems = [];
        $skipByExclude = false;
        $handle = opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                preg_match("/(^(([\.]) {1,2})$|(\.(svn|git|md|htaccess))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
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
                            $arrayItems = array_merge($arrayItems, self::directory_to_array($directory . DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude, $allowed_filetypes, $relative_path));
                        }
                        if ($listDirs) {
                            $arrayItems[] = ( $relative_path ? $file : $directory . DIRECTORY_SEPARATOR . $file );
                        }
                    } else {
                        if ($listFiles) {
                            $arrayItems[] = ( $relative_path ? $file : $directory . DIRECTORY_SEPARATOR . $file );
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
     * @param boolean $DELETE_DIRECTORY Optinal, if is set directory will be deleted too. 
     */
    public static function delete_directory_contents($directory, $DELETE_DIRECTORY = FALSE) {
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            $path = util::get_path([ $directory, $file]);
            ( is_dir($path) ? self::delete_directory_contents($path, TRUE) : unlink($path));
        }

        return ( $DELETE_DIRECTORY ? rmdir($directory) : TRUE );
    }

    /**
     * Get the headers send with client's HTTP Request
     * @return array Return the array with the headers (indexes in lowercase)
     */
    public static function headers() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } else if ($name == 'CONTENT_TYPE') {
                $headers['Content-Type'] = $value;
            } else if ($name == 'CONTENT_LENGTH') {
                $headers['Content-Length'] = $value;
            }
        }
        return $headers;
    }

    /**
     * Create a random readable word
     * @param integer $length String's length
     * @return string
     */
    public static function readable_random_string($length = 8) {

        $conso = [ 'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'];
        $vocal = [ 'a', 'e', 'i', 'o', 'u'];

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
     * @param str #url
     * @return array
     */
    public static function curl_headers($url, &$data) {
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
     * @param str $url, $path
     * @return bool True if download succeed 
     */
    public static function curl_download($url, $path, $timeout = 9000000000) {
        $return = FALSE;
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
            return FALSE;
        }
        return $return;
    }

    /**
     * Create a temporary file path
     * @param String $prefix Prefix of the filename
     * @return String The path of the temporary filename
     */
    public static function tempfile($prefix) {
        global $settings;
        $folder = '/tmp';
        if (isset($settings['temporary_folder'])) {
            $folder = $settings['temporary_folder'];
        }
        return tempnam($folder, $prefix);
    }

    /**
     * Extract extension from a filename
     * @param String $filename The filename
     * @return String The extension without dot prefix
     */
    public static function extension($filename) {
        return strtolower(preg_replace('/^.*\.([^.]+)$/D', '$1', $filename));
    }

    /**
     * Join directories and filename to create path
     * @param array $array Array with directories and filename for example array( '/tmp', 'me', 'file.tmp' )
     * @return String Path
     */
    public static function get_path($array, $glue = DIRECTORY_SEPARATOR) {
        return str_replace('\\\\', '\\', join($glue, $array));
    }

    public static function to_single_slashes($input) {
        return preg_replace('~(^|[^:])//+~', '\\1/', $input);
    }

    public static function parse_regexp($input, $pattern) {
        preg_match($pattern, $input, $matches);
        return $matches;
    }

    /**
     * Get the size of a file
     * Works for large files too (>2GB )
     * @param $path File's path
     */
    public static function get_file_size($path) {
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
        return floatval($size);
    }

    /*
      function getFilename($url) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_NOBODY, 1);
      $data = curl_exec($ch);

      preg_match("#filename=([^\n]+)#is", $data, $matches);

      return $matches[1];
      } */
}