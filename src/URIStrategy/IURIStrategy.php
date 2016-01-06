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
namespace Phramework\URIStrategy;

/**
 * IURIStrategy Interface
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 1.0.0
 */
interface IURIStrategy
{
    /**
     * Invoke URIStrategy, the implementation of this method MUST associate the
     * request and invoke the apropriate method to handle this request.
     * Invokes to handler method MUST at least define
     * `$requestParameters, $requestMethod, $requestHeaders` as arguments.
     * NotFoundException SHOULD be when associatation between the request and
     * handler is not defined
     * UnauthorizedException SHOULD be thrown when a request requires authorization
     * @param  object       $requestParameters Request parameters
     * @param  string       $requestMethod     HTTP request method
     * @param  array        $requestHeaders    Request headers
     * @param  object|false $requestUser       Use object if successful
     * authenticated otherwise false
     * @return string[2] This method SHOULD return a tuple specifing at least
     * `[$class, $method]` on success.
     * @throws Phramework\Exceptions\NotFoundException
     * @throws Phramework\Exceptions\UnauthorizedException
     */
    public function invoke(
        &$requestParameters,
        $requestMethod,
        $requestHeaders,
        $requestUser
    );
}
