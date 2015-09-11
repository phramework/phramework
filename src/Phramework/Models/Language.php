<?php

namespace Phramework\Models;

/**
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @package Phramework
 * @category Models
 */
class Language
{
    /**
     * Replace %key% from template string with their value from parameters array
     */
    public static function template($template, $parameters, $start_char = '%', $end_char = '%')
    {
        foreach ($parameters as $key => $value) {
            $template = str_replace($start_char . $key . $end_char, $value, $template);
        }
        return $template;
    }
}
