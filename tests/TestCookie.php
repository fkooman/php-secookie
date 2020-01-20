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

use fkooman\SeCookie\Cookie;
use fkooman\SeCookie\CookieOptions;

class TestCookie extends Cookie
{
    /** @var array<string> */
    private $headersSent = [];

    /** @var array<string> */
    private $cookieData;

    /**
     * @param array<string,string> $cookieData
     */
    public function __construct(CookieOptions $cookieOptions, array $cookieData = [])
    {
        parent::__construct($cookieOptions);
        $this->cookieData = $cookieData;
    }

    /**
     * @return array<string>
     */
    public function getHeadersSent()
    {
        return $this->headersSent;
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

    /**
     * @param string $cookieName
     *
     * @return string|null
     */
    protected function readCookie($cookieName)
    {
        if (!\array_key_exists($cookieName, $this->cookieData)) {
            return null;
        }

        return $this->cookieData[$cookieName];
    }
}
