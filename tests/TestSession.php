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

namespace fkooman\SeCookie\Tests;

use DateTime;
use fkooman\SeCookie\Session;
use fkooman\SeCookie\SessionOptions;

class TestSession extends Session
{
    /** @var array<string> */
    private $headersSent = [];

    /** @var int */
    private $randomCount = 0;

    /**
     * @param int $randomStartIndex
     */
    public function __construct(SessionOptions $sessionOptions, TestCookie $cookie, TestSessionStorage $sessionStorage, $randomStartIndex, DateTime $dateTime)
    {
        parent::__construct($sessionOptions, $cookie);
        $this->randomCount = $randomStartIndex;
        $this->sessionStorage = $sessionStorage;
        $this->dateTime = $dateTime;
    }

    /**
     * @return array<string>
     */
    public function getHeadersSent()
    {
        return $this->headersSent;
    }

    /**
     * @return string
     */
    protected function getRandomBytes()
    {
        return \str_repeat(\pack('C', $this->randomCount++), 32);
    }

    /**
     * @param string $headerKeyValue
     *
     * @return void
     */
    protected function sendHeader($headerKeyValue)
    {
        $this->headersSent[] = $headerKeyValue;
    }
}
