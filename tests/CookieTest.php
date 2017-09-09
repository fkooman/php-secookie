<?php

/**
 * Copyright (c) 2017 François Kooman <fkooman@tuxed.net>.
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
use PHPUnit_Framework_TestCase;

class CookieTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testSimple()
    {
    }

    /**
     * @return void
     */
    public function testDeleteCookie()
    {
        $t = new TestHeader();
        $c = new Cookie([], $t);
        $c->delete('foo');
        $this->assertSame(
            [
                'Set-Cookie: foo=; Secure; HttpOnly; Max-Age=0; SameSite=Strict',
            ],
            $t->ls()
        );
    }

    /**
     * @return void
     */
    public function testDeleteCookieWithMaxAge()
    {
        $t = new TestHeader();
        $c = new Cookie(['Max-Age' => 12345], $t);
        $c->delete('foo');
        $this->assertSame(
            [
                'Set-Cookie: foo=; Secure; HttpOnly; Max-Age=0; SameSite=Strict',
            ],
            $t->ls()
        );
    }

    /**
     * @return void
     */
    public function testAttributeValues()
    {
        $t = new TestHeader();
        $c = new Cookie(
            [
                'Path' => '/foo/',
                'Domain' => 'www.example.org',
                'Max-Age' => 12345,
            ],
            $t
        );
        $c->set('foo', 'bar');
        $this->assertSame(
            [
                'Set-Cookie: foo=bar; Secure; HttpOnly; Path=/foo/; Domain=www.example.org; Max-Age=12345; SameSite=Strict',
            ],
            $t->ls()
        );
    }

    /**
     * @return void
     */
    public function testDomain()
    {
    }
}
