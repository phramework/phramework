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
 * @deprecated since 2.0.0
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
     * @uses htmlentities
     * @param string $content
     * @return string
     * @deprecated since 2.0.0
     */
    public static function userContent($content)
    {
        return htmlentities($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Create a temporary file path
     * @param string $prefix Prefix of the filename
     * @return string The path of the temporary filename
     * @deprecated since 2.0.0
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
     * @deprecated since 2.0.0
     */
    public static function toSingleSlashes($input)
    {
        return preg_replace('~(^|[^:])//+~', '\\1/', $input);
    }

    /**
     * @deprecated since 2.0.0
     */
    public static function parseRegexp($input, $pattern)
    {
        preg_match($pattern, $input, $matches);
        return $matches;
    }
}
