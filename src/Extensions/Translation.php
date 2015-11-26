<?php
/**
 * Copyright 2015 Spafaridis Xenofon
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
namespace Phramework\Extensions;

/**
 * translation extension
 *
 * Dummy implementation should be extended
 * used to translate keyword into multiple languages
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 * @deprecated
 */
class Translation
{
    protected $strings = [];
    protected $track_missing_keys;
    protected $language_code;

    public function __construct($language_code, $track_missing_keys = false)
    {
        $this->language_code = $language_code;
        $this->track_missing_keys = $track_missing_keys;
    }

    public function setLanguageCode($language_code)
    {
        $this->language_code = $language_code;
    }

    /**
     * Translate a string
     *
     * @param string $key
     * @param array|NULL $parameters
     * @param string $fallback_value
     * @return string Returns the translated string
     */
    public function getTranslated($key, $parameters = null, $fallback_value = null)
    {
        $translated = null;

        //if translations is set for this key
        if (!isset($this->strings[$key])) {
            //Track not found strings
            if ($this->track_missing_keys) {
                $this->add_key($key);
            }

            //use $fallback_value is provided else use the key
            $translated = ($fallback_value ? $fallback_value : $key);
        } else {
            $translated = $this->strings[$key];
        }

        if ($parameters) {
            $translated = \Phramework\Models\language::template($translated, $parameters);
        }

        return $translated;
    }

    /**
     * Report untranslated key
     * @param type $key
     */
    public function addKey($key)
    {
    }
}
