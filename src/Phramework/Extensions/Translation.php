<?php

namespace Phramework\Extensions;

/**
 * translation extension
 *
 * Dummy implementation should be extended
 * used to translate keyword into multiple languages
 * @author Xenophon Spafaridis <nohponex@gmail.com>
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
