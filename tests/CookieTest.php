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
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
    /**
     * @return void
     */
    public function testNoCookie()
    {
        $cookieOptions = new CookieOptions();
        $testCookie = new TestCookie($cookieOptions, []);

        $this->assertSame([], $testCookie->getHeadersSent());
    }

    /**
     * @return void
     */
    public function testSimple()
    {
        $cookieOptions = new CookieOptions();
        $testCookie = new TestCookie($cookieOptions, []);
        $testCookie->set('foo', 'bar');
        $this->assertSame(
            [
                'Set-Cookie: foo=bar; HttpOnly; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
    }

    /**
     * @return void
     */
    public function testDeleteCookie()
    {
        $cookieOptions = new CookieOptions();
        $testCookie = new TestCookie($cookieOptions, []);
        $testCookie->delete('foo');
        $this->assertSame(
            [
                'Set-Cookie: foo=; HttpOnly; Max-Age=0; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
    }

    /**
     * @return void
     */
    public function testDeleteCookieWithMaxAge()
    {
        $cookieOptions = new CookieOptions();
        $cookieOptions->setMaxAge(12345);
        $testCookie = new TestCookie($cookieOptions, []);
        $testCookie->delete('foo');
        $this->assertSame(
            [
                'Set-Cookie: foo=; HttpOnly; Max-Age=0; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
    }

    /**
     * @return void
     */
    public function testAttributeValues()
    {
        $cookieOptions = new CookieOptions();
        $cookieOptions->setPath('/foo/');
        $cookieOptions->setMaxAge(12345);
        $testCookie = new TestCookie($cookieOptions, []);
        $testCookie->set('foo', 'bar');
        $this->assertSame(
            [
                'Set-Cookie: foo=bar; HttpOnly; Max-Age=12345; Path=/foo/; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
    }

    /**
     * @return void
     */
    public function testGetCookie()
    {
        $cookieOptions = new CookieOptions();
        $testCookie = new TestCookie($cookieOptions, ['SID' => 'abcdef']);
        $this->assertSame('abcdef', $testCookie->get('SID'));
    }

    /**
     * @return void
     */
    public function testMissingCookie()
    {
        $cookieOptions = new CookieOptions();
        $testCookie = new TestCookie($cookieOptions, []);
        $this->assertNull($testCookie->get('SID'));
    }

    /**
     * @return void
     */
    public function testSetSameSiteNoneCookie()
    {
        $cookieOptions = CookieOptions::init()->setSameSite('None');
        $testCookie = new TestCookie($cookieOptions, []);
        $testCookie->set('foo', 'bar');
        $this->assertSame(
            [
                'Set-Cookie: foo=bar; HttpOnly; SameSite=None; Secure',
                'Set-Cookie: foo'.Cookie::NO_SAME_SITE_POSTFIX.'=bar; HttpOnly; Secure',
            ],
            $testCookie->getHeadersSent()
        );
    }

    /**
     * @return void
     */
    public function testGetSameSiteNoneCookie()
    {
        $cookieOptions = CookieOptions::init()->setSameSite('None');
        // "foo" cookie should take precedence
        $testCookie = new TestCookie($cookieOptions, ['foo'.Cookie::NO_SAME_SITE_POSTFIX => 'bar', 'foo' => 'baz']);
        $this->assertSame('baz', $testCookie->get('foo'));
        $testCookie = new TestCookie($cookieOptions, ['foo'.Cookie::NO_SAME_SITE_POSTFIX => 'bar']);
        $this->assertSame('bar', $testCookie->get('foo'));
    }
}
