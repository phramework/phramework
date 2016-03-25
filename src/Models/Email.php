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
 * Email functions
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 * @todo add more methods
 * @todo add defined settings documentation
 */
class Email
{
    /**
     * Send an e-mail
     * @uses mail
     * @param string $address
     * @param string $subject
     * @param string $body
     * @param string $account *[Optional]*, Account name
     * @throws \Exception When email setting is not set
     */
    public static function send($address, $subject, $body, $account = 'default')
    {
        $HTML     = true;
        $accounts = \Phramework\Phramework::getSetting('email');

        if (empty($accounts) || !isset($accounts['default'])) {
            throw new \Exception('email setting is required');
        }

        if (!isset($accounts[$account])) {
            $account = 'default';
        }

        $headers   = [];
        $headers[] = 'MIME-Version: 1.0' . "\r\n";

        if (!$HTML) {
            $headers[] = 'Content-Type: text/plain;charset=utf-8' . "\r\n";
        } else {
            $headers[] = 'Content-Type: text/html;charset=utf-8' . "\r\n";
        }

        $headers[] = 'From: ' . $accounts[$account]['name'] . ' <' . $accounts[$account]['mail'] . '>' . "\r\n";
        $headers[] = 'Reply-To: ' . $accounts[$account]['name'] . ' <' . $accounts[$account]['mail'] . "\r\n";

        mail(
            $address,
            $subject,
            $body,
            implode('', $headers),
            ('-f' . $accounts[$account]['mail'])
        );
    }
}
