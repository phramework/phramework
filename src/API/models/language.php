<?php

namespace Phramework\API\models;

/**
 * Return translated string
 */
/* function __( $key ) {
  global $strings;

  if( $strings && array_key_exists( $key, $strings ) ) {
  return $strings[ $key ];
  }

  //Track not found strings
  //global $translate;
  global $settings;
  //$translate->add_key( $settings[ 'translate' ][ 'project_id'], $key );

  return $key;
  } */

/**
 * Print tranlated string
 */
/* function ___( $key ) {
  global $strings;

  if( $strings && array_key_exists( $key, $strings ) ) {
  echo $strings[ $key ];
  return;
  }

  //Track not found strings
  //global $translate;
  //global $settings;
  //$translate->add_key( $settings[ 'translate' ][ 'project_id'], $key );

  echo $key;
  } */

/**
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @package Phramework
 * @subpackage API
 * @category models
 */
class language {

    /**
     * Replace %key% from template string with their value from parameters array  
     */
    public static function template($template, $parameters) {

        foreach ($parameters as $key => $value) {
            $template = str_replace('%' . $key . '%', $value, $template);
        }
        return $template;
    }

}
