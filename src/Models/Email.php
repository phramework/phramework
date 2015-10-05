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
namespace Phramework\Models;

/**
 * Email functions
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @category Models
 * @todo add more methods
 */
class Email
{
    /* public static function send( $address, $subject, $body, $account = 'contact@host.com' ) {

      $HTML = TRUE;
      global $settings;
      if( !isset( $settings[ 'addresses' ][ $account ] )
      || !isset( $settings[ 'addresses' ][ $account ][ 'address' ] )
      || !isset( $settings[ 'addresses' ][ $account ][ 'name' ] ) ) {
      write_error_log( 'mail send account not found : ' . $account );
      $account = 'contact';
      }
      // Turn off all error reporting
      error_reporting(0);

      require_once "Mail.php";
      require_once "Mail/mime.php";

      $from = $settings[ 'addresses' ][ $account ][ 'name' ]
      .   ' <' . $settings[ 'addresses' ][ $account ][ 'address' ]  . '>';
      $to = '<' . $address . '>';
      //$subject = $subject;
      $headers = array(
      'From' => $from,
      'To' => $to,
      'Subject' => $subject,
      );

      if( $HTML ) {
      global $language;
      $temp_language = $language;
      $language = $lang;
      clude( 'viewers/email/email.php' );
      $body = emailViewer::view( array( 'subject' => $subject, 'body' => $body, 'address' => $address ) );
      $language = $temp_language;
      //echo $body;
      $mime = new Mail_mime( "\n" );

      $mime->setHTMLBody($body);

      $mimeparams=array();

      $mimeparams['text_encoding']="7bit";
      $mimeparams['text_charset']="UTF-8";
      $mimeparams['html_charset']="UTF-8";
      $mimeparams['head_charset']="UTF-8";



      $body = $mime->get($mimeparams);
      // Setting the body of the email
      //$mime->setTXTBody($text);
      if( $cc ) {
      $mime->addCc( $cc ); // optional
      }
      if( $bcc ) {
      $mime->addBcc( $bcc ); //optional
      }

      //$body = $mime->get();
      $headers = $mime->headers($headers);
      }

      $smtp = Mail::factory('smtp', array(
      'host' => 'ssl://smtp.gmail.com',
      'port' => '465',
      'auth' => true,
      'username' => $settings[ 'smtp' ][ 'username' ],
      'password' => base64_decode( $settings[ 'smtp' ][ 'password' ] )
      ));

      $mail = $smtp->send($to, $headers, $body);

      if (PEAR::isError($mail)) {
      return FALSE;
      //echo('<p>' . $mail->getMessage() . '</p>');
      }
      return TRUE;
      //echo('<p>Message successfully sent!</p>');

      } */

    /**
     * Send an e-mail
     *
     * @param string $address
     * @param string $subject
     * @param string $body
     * @param string $account Account name. Optional, default is default
     */
    public static function send($address, $subject, $body, $account = 'default')
    {
        $HTML     = true;
        $accounts = \Phramework\API::getSetting('email');

        if (!$accounts || !isset($accounts['default'])) {
            throw new \Exception('email setting is required');
        }

        if (!isset($accounts[$account])) {
            $account = 'default';
        }

        $headers   = [];
        $headers[] = "MIME-Version: 1.0" . "\r\n";

        if (!$HTML) {
            $headers[] = 'Content-type: text/plain;charset=utf-8' . "\r\n";
        } else {
            $headers[] = 'Content-type: text/html;charset=utf-8' . "\r\n";
        }

        $headers[] = 'From: ' . $accounts[$account]['name'] . ' <' . $accounts[$account]['mail'] . '>' . "\r\n";
        $headers[] = 'Reply-To: ' . $accounts[$account]['name'] . ' <' . $accounts[$account]['mail'] . "\r\n";

        mail($address, $subject, $body, implode('', $headers), ('-f' . $accounts[$account]['mail']));
    }
}
