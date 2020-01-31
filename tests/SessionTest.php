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
use fkooman\SeCookie\CookieOptions;
use fkooman\SeCookie\SessionOptions;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @return void
     */
    public function testSessionStart()
    {
        $testSessionStorage = new TestSessionStorage([]);
        $testCookie = new TestCookie(new CookieOptions(), []);
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 0, new DateTime('2019-01-01T08:00:00+00:00'));

        $testSession->start();
        $testSession->stop();
        $this->assertSame(
            [
                'Set-Cookie: SID=0000000000000000000000000000000000000000000000000000000000000000; HttpOnly; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
        $this->assertSame(
            [
                'Cache-Control: no-store, no-cache, must-revalidate',
                'Pragma: no-cache',
            ],
            $testSession->getHeadersSent()
        );
        $this->assertSame(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                ],
            ],
            $testSessionStorage->getAll()
        );
    }

    /**
     * @return void
     */
    public function testSetValue()
    {
        $testSessionStorage = new TestSessionStorage([]);
        $testCookie = new TestCookie(new CookieOptions(), []);
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 0, new DateTime('2019-01-01T08:00:00+00:00'));

        $testSession->start();
        $testSession->set('foo', 'bar');
        $testSession->stop();
        $this->assertSame(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                   'foo' => 'bar',
                ],
            ],
            $testSessionStorage->getAll()
        );
        $this->assertSame(
            [
                'Set-Cookie: SID=0000000000000000000000000000000000000000000000000000000000000000; HttpOnly; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
    }

    /**
     * @return void
     */
    public function testGetValue()
    {
        $testSessionStorage = new TestSessionStorage(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                    'foo' => 'bar',
                ],
            ]
        );
        $testCookie = new TestCookie(new CookieOptions(), ['SID' => '0000000000000000000000000000000000000000000000000000000000000000']);
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 0, new DateTime('2019-01-01T08:00:00+00:00'));

        $testSession->start();
        $this->assertSame('bar', $testSession->get('foo'));
        // this is a continuation of a session, so we MUST NOT set a new session cookie
        $this->assertSame([], $testCookie->getHeadersSent());
    }

    /**
     * @return void
     */
    public function testUnsetValue()
    {
        $testSessionStorage = new TestSessionStorage(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                    'foo' => 'bar',
                ],
            ]
        );
        $testCookie = new TestCookie(new CookieOptions(), ['SID' => '0000000000000000000000000000000000000000000000000000000000000000']);
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 0, new DateTime('2019-01-01T08:00:00+00:00'));

        $testSession->start();
        $this->assertSame('bar', $testSession->get('foo'));
        $testSession->remove('foo');
        $testSession->stop();

        $this->assertSame(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                ],
            ],
            $testSessionStorage->getAll()
        );
        $this->assertSame([], $testCookie->getHeadersSent());
    }

    /**
     * @return void
     */
    public function testRegenerate()
    {
        $testSessionStorage = new TestSessionStorage(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                    'foo' => 'bar',
                ],
            ]
        );
        $testCookie = new TestCookie(new CookieOptions(), ['SID' => '0000000000000000000000000000000000000000000000000000000000000000']);
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 1, new DateTime('2019-01-01T08:15:00+00:00'));

        $testSession->start();
        $testSession->regenerate();
        $testSession->stop();

        // the "0000000000000000000000000000000000000000000000000000000000000000" session should be gone now
        $this->assertSame(
            [
                '0101010101010101010101010101010101010101010101010101010101010101' => [
                    // __expires_at is again 8 hours in the future from the
                    // current time, as we set that to 10:00, will be 18:00
                    '__expires_at' => '2019-01-01T08:45:00+00:00',
                    'foo' => 'bar',
                ],
            ],
            $testSessionStorage->getAll()
        );

        // we regenerated the session ID, so we expect a new session cookie
        // we expect a new cookie to be sent
        $this->assertSame(
            [
                'Set-Cookie: SID=0101010101010101010101010101010101010101010101010101010101010101; HttpOnly; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
    }

    /**
     * @return void
     */
    public function testNonExistingSession()
    {
        $testSessionStorage = new TestSessionStorage([]);
        $testCookie = new TestCookie(new CookieOptions(), ['SID' => '0000000000000000000000000000000000000000000000000000000000000000']);
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 1, new DateTime('2019-01-01T08:00:00+00:00'));

        $testSession->start();
        $testSession->stop();
        // we expect a new cookie to be sent as the provided cookie does not have an active session
        $this->assertSame(
            [
                'Set-Cookie: SID=0101010101010101010101010101010101010101010101010101010101010101; HttpOnly; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
        $this->assertSame(
            [
                '0101010101010101010101010101010101010101010101010101010101010101' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                ],
            ],
            $testSessionStorage->getAll()
        );
    }

    /**
     * @return void
     */
    public function testExpiredSession()
    {
        $testSessionStorage = new TestSessionStorage(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                    'foo' => 'bar',
                ],
            ]
        );
        $testCookie = new TestCookie(new CookieOptions(), ['SID' => '0000000000000000000000000000000000000000000000000000000000000000']);
        // session should have been expired 15 minutes ago...
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 1, new DateTime('2019-01-01T08:45:00+00:00'));

        $testSession->start();
        $this->assertNull($testSession->get('foo'));
        $testSession->stop();
        $this->assertSame(
            [
                'Set-Cookie: SID=0101010101010101010101010101010101010101010101010101010101010101; HttpOnly; SameSite=Lax; Secure',
            ],
            $testCookie->getHeadersSent()
        );
        $this->assertSame(
            [
                '0101010101010101010101010101010101010101010101010101010101010101' => [
                    '__expires_at' => '2019-01-01T09:15:00+00:00',
                ],
            ],
            $testSessionStorage->getAll()
        );
    }

    /**
     * @return void
     */
    public function testDestroy()
    {
        $testSessionStorage = new TestSessionStorage(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                    'foo' => 'bar',
                ],
            ]
        );
        $testCookie = new TestCookie(new CookieOptions(), ['SID' => '0000000000000000000000000000000000000000000000000000000000000000']);
        $testSession = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 0, new DateTime('2019-01-01T08:00:00+00:00'));

        $testSession->start();
        $testSession->destroy();
        $this->assertSame([], $testSessionStorage->getAll());
    }

    /**
     * @return void
     */
    public function testMultipleSessions()
    {
        $testSessionStorage = new TestSessionStorage(
            [
                '0000000000000000000000000000000000000000000000000000000000000000' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                    'foo' => 'bar',
                ],
                '0101010101010101010101010101010101010101010101010101010101010101' => [
                    '__expires_at' => '2019-01-01T08:30:00+00:00',
                    'foo' => 'baz',
                ],
            ]
        );
        $testCookie = new TestCookie(
            new CookieOptions(),
            [
                'SID' => '0000000000000000000000000000000000000000000000000000000000000000',
                'TID' => '0101010101010101010101010101010101010101010101010101010101010101',
            ]
        );
        $testSessionOne = new TestSession(new SessionOptions(), $testCookie, $testSessionStorage, 0, new DateTime('2019-01-01T08:00:00+00:00'));
        $testSessionTwo = new TestSession(SessionOptions::init()->withName('TID'), $testCookie, $testSessionStorage, 0, new DateTime('2019-01-01T08:00:00+00:00'));

        $testSessionOne->start();
        $this->assertSame('bar', $testSessionOne->get('foo'));

        $testSessionTwo->start();
        $this->assertSame('baz', $testSessionTwo->get('foo'));

        // this is a continuation of a sessions, so we MUST NOT set a new session cookie
        $this->assertSame([], $testCookie->getHeadersSent());
    }
}
