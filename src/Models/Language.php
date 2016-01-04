<?php
/**
 * Copyright 2015 Xenofon Spafaridis
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
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class Language
{
    /**
     * Replace %key% from template string with their value from parameters array
     * @param  string $template
     * @param  string $parameters
     * @param  string $startChar  *[Optional]*
     * @param  string $endChar    *[Optional]*
     * @return string
     */
    public static function template(
        $template,
        $parameters,
        $startChar = '%',
        $endChar = '%'
    ) {
        foreach ($parameters as $key => $value) {
            $template = str_replace($startChar . $key . $endChar, $value, $template);
        }
        return $template;
    }
}
