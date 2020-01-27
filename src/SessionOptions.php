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

use DateInterval;

class SessionOptions
{
    /** @var string */
    const SESSION_EXPIRY_DEFAULT = 'PT30M';

    /** @var string */
    private $sessionName = 'SID';

    /** @var \DateInterval */
    private $expiresIn;

    /** @var bool */
    private $garbageCollection = true;

    public function __construct()
    {
        $this->expiresIn = new DateInterval(self::SESSION_EXPIRY_DEFAULT);
    }

    /**
     * @return self
     */
    public static function init()
    {
        return new self();
    }

    /**
     * @param string $sessionName
     *
     * @return self
     */
    public function setName($sessionName)
    {
        $this->sessionName = $sessionName;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->sessionName;
    }

    /**
     * @return void
     */
    public function setExpiresIn(DateInterval $expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return \DateInterval
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @return void
     */
    public function disableGc()
    {
        $this->garbageCollection = false;
    }

    /**
     * @return bool
     */
    public function getGc()
    {
        return $this->garbageCollection;
    }
}
