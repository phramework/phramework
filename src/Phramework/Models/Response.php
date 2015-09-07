<?php
namespace Phramework\Models;

use Phramework\API;

/**
 * Response class
 *
 * Provides function related to server response
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0.4
 * @package Phramework
 * @subpackage API
 * @category models
 */
class Response
{
    /**
     * Responde with 204 No Content Status-Code
     *
     * The server has fulfilled the request but does not need to return an entity-body,
     * and might want to return updated metainformation.
     * The response MAY include new or updated metainformation in the form of entity-headers,
     * which if present SHOULD be associated with the requested variant.
     * If the client is a user agent, it SHOULD NOT change its document view from that
     * which caused the request to be sent. This response is primarily intended to allow
     * input for actions to take place without causing a change to the user
     * agent's active document view, although any new or updated metainformation SHOULD be
     * applied to the document currently in the user agent's active view.
     */
    public static function noContent()
    {
        if (!headers_sent()) {
            //header('HTTP/1.0 204 No Content', true, 204);
            http_response_code(204);
        } else {
            throw new \Phramework\Exceptions\Server();
        }
    }

    /**
     * Responde with 201 Created Status-Code
     *
     * The request has been fulfilled and resulted in a new resource being created.
     * The newly created resource can be referenced by the URI(s) returned in
     * the entity of the response, with the most specific URI for the
     * resource given by a Location header field.
     * @param  String $location URI to newly created resouce
     */
    public static function created($location)
    {
        if (!headers_sent()) {
            http_response_code(201);
            header('Location: ' . $location);
        } else {
            throw new \Phramework\Exceptions\Server();
        }
    }

    /**
     * Responde with 202 Accepted
     *
     *  The request has been accepted for processing,
     *  but the processing has not been completed.
     *  The request might or might not eventually be acted upon,
     *  as it might be disallowed when processing actually takes place.
     *  There is no facility for re-sending a status code
     *  from an asynchronous operation such as this.
     * @param  String $location URI to newly created resouce
     */
    public static function accepted()
    {
        if (!headers_sent()) {
            http_response_code(202);
        } else {
            throw new \Phramework\Exceptions\Server();
        }
    }

    /**
     * Write cache headers
     */
    public static function cacheHeaders($expires = '+1 hour')
    {
        if (!headers_sent()) {
            header('Cache-Control: private, max-age=3600');
            header('Pragma: public');
            header('Last-Modified: ' . date(DATE_RFC822, strtotime('-1 second')));
            header('Expires: ' . date(DATE_RFC822, strtotime($expires)));
        }
    }
}
