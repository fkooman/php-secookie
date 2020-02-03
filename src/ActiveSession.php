<?php

/*
 * Copyright (c) 2017-2020 François Kooman <fkooman@tuxed.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace fkooman\SeCookie;

use DateTime;
use fkooman\SeCookie\Exception\SessionException;

class ActiveSession
{
    /** @var string */
    private $sessionId;

    /** @var array */
    private $sessionData;

    /**
     * @param string $sessionId
     */
    public function __construct($sessionId, array $sessionData)
    {
        $this->sessionId = $sessionId;
        $this->sessionData = $sessionData;
    }

    /**
     * @return string
     */
    public function sessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return array
     */
    public function sessionData()
    {
        return $this->sessionData;
    }

    /**
     * @param string $sessionKey
     *
     * @return void
     */
    public function remove($sessionKey)
    {
        if (\array_key_exists($sessionKey, $this->sessionData)) {
            unset($this->sessionData[$sessionKey]);
        }
    }

    /**
     * @param string $sessionKey
     * @param string $sessionValue
     *
     * @return void
     */
    public function set($sessionKey, $sessionValue)
    {
        $this->sessionData[$sessionKey] = $sessionValue;
    }

    /**
     * @param string $sessionKey
     *
     * @return string|null
     */
    public function get($sessionKey)
    {
        if (!\array_key_exists($sessionKey, $this->sessionData)) {
            return null;
        }
        /** @var mixed $sessionValue */
        $sessionValue = $this->sessionData[$sessionKey];
        if (!\is_string($sessionValue)) {
            // if the value is NOT a string, pretend it is not there...
            return null;
        }

        return $sessionValue;
    }

    /**
     * @return bool
     */
    public function isExpired(DateTime $dateTime)
    {
        if (null === $sessionExpiresAt = $this->get('__expires_at')) {
            throw new SessionException('missing "__expires_at" in session data');
        }

        $expiresAt = new DateTime($sessionExpiresAt);

        return $dateTime->getTimestamp() >= $expiresAt->getTimestamp();
    }
}
