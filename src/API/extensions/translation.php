<?php

namespace Phramework\API\extensions;

/**
 * translation extension
 * 
 * Dummy implementation should be extended
 * used to translate keyword into multiple languages
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 */
class translation {

    protected $strings = [];
    protected $track_missing_keys;
    protected $language_code;

    public function __construct($language_code, $track_missing_keys = FALSE) {
        $this->language_code = $language_code;
        $this->track_missing_keys = $track_missing_keys;
    }

    public function set_language_code($language_code) {
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
    public function get_translated($key, $parameters = NULL, $fallback_value = NULL) {
        $translated = NULL;

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
            $translated = \Phramework\API\models\language::template($translated, $parameters);
        }

        return $translated;
    }

    /**
     * Report untranslated key
     * @param type $key
     */
    public function add_key($key) {
        
    }

}