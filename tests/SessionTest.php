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
use fkooman\SeCookie\Session;
use PHPUnit_Framework_TestCase;

class SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testSimple()
    {
        $t = new TestHeader();
        $c = new Session([], new Cookie([], $t));
        $c->set('foo', 'bar');
        $this->assertSame(
            [
                sprintf('Set-Cookie: PHPSESSID=%s; Secure; HttpOnly; SameSite=Strict', $c->id()),
            ],
            $t->ls()
        );
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testSessionName()
    {
        $t = new TestHeader();
        $c = new Session(['SessionName' => 'SID'], new Cookie([], $t));
        $c->set('foo', 'bar');
        $this->assertSame(
            [
                sprintf('Set-Cookie: SID=%s; Secure; HttpOnly; SameSite=Strict', $c->id()),
            ],
            $t->ls()
        );
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testRegenerate()
    {
        $t = new TestHeader();
        $c = new Session([], new Cookie([], $t));
        $sessionId = $c->id();
        $this->assertSame(
            [
                sprintf('Set-Cookie: PHPSESSID=%s; Secure; HttpOnly; SameSite=Strict', $sessionId),
            ],
            $t->ls()
        );
        $c->regenerate();
        $sessionId = $c->id();
        $this->assertSame(
            [
                sprintf('Set-Cookie: PHPSESSID=%s; Secure; HttpOnly; SameSite=Strict', $sessionId),
            ],
            $t->ls()
        );
    }

    /**
     * @runInSeparateProcess
     * @expectedException        \fkooman\SeCookie\Exception\SessionException
     * @expectedExceptionMessage session bound to DomainBinding, we got "www.example.org", but expected "www.example.com"
     *
     * @return void
     */
    public function testDomainBinding()
    {
        $t = new TestHeader();
        $c = new Session(
            [
                'DomainBinding' => 'www.example.org',
            ],
            new Cookie([], $t)
        );
        $c = new Session(
            [
                'DomainBinding' => 'www.example.com',
            ],
            new Cookie([], $t)
        );
    }

    /**
     * @runInSeparateProcess
     * @expectedException        \fkooman\SeCookie\Exception\SessionException
     * @expectedExceptionMessage session bound to PathBinding, we got "/foo/", but expected "/bar/"
     *
     * @return void
     */
    public function testPathBinding()
    {
        $t = new TestHeader();
        $c = new Session(
            [
                'PathBinding' => '/foo/',
            ],
            new Cookie([], $t)
        );
        $c = new Session(
            [
                'PathBinding' => '/bar/',
            ],
            new Cookie([], $t)
        );
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testSetGet()
    {
        $t = new TestHeader();
        $c = new Session([], new Cookie([], $t));
        $c->set('foo', 'bar');
        $this->assertSame('bar', $c->get('foo'));
    }

    /**
     * @runInSeparateProcess
     * @expectedException        \fkooman\SeCookie\Exception\SessionException
     * @expectedExceptionMessage key "foo" not available in session
     *
     * @return void
     */
    public function testGetMissing()
    {
        $t = new TestHeader();
        $c = new Session([], new Cookie([], $t));
        $c->get('foo');
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testDelete()
    {
        $t = new TestHeader();
        $c = new Session([], new Cookie([], $t));
        $c->set('foo', 'bar');
        $this->assertTrue($c->has('foo'));
        $c->delete('foo');
        $this->assertFalse($c->has('foo'));
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testDeleteMissing()
    {
        $t = new TestHeader();
        $c = new Session([], new Cookie([], $t));
        $this->assertFalse($c->has('foo'));
        $c->delete('foo');
        $this->assertFalse($c->has('foo'));
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testExpiredCanary()
    {
        $t = new TestHeader();
        $c = new Session(
            [
                'CanaryExpiry' => 'PT01S',
            ],
            new Cookie([], $t)
        );
        $c->set('foo', 'bar');
        $firstId = $c->id();
        sleep(2);
        $c = new Session(
            [
                'CanaryExpiry' => 'PT01S',
            ],
            new Cookie([], $t)
        );
        $secondId = $c->id();
        $this->assertNotSame($firstId, $secondId);
        $this->assertTrue($c->has('foo'));
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testNotExpiredCanary()
    {
        $t = new TestHeader();
        $c = new Session(
            [
                'CanaryExpiry' => 'PT01S',
            ],
            new Cookie([], $t)
        );
        $firstId = $c->id();
        $c = new Session(
            [
                'CanaryExpiry' => 'PT01S',
            ],
            new Cookie([], $t)
        );
        $secondId = $c->id();
        $this->assertSame($firstId, $secondId);
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testExpiredSession()
    {
        $t = new TestHeader();
        $c = new Session(
            [
                'SessionExpiry' => 'PT01S',
            ],
            new Cookie([], $t)
        );
        $c->set('foo', 'bar');
        $this->assertTrue($c->has('foo'));
        sleep(2);
        $c = new Session(
            [
                'SessionExpiry' => 'PT01S',
            ],
            new Cookie([], $t)
        );
        $this->assertFalse($c->has('foo'));
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testDestroy()
    {
        $t = new TestHeader();
        $c = new Session([], new Cookie([], $t));
        $firstId = $c->id();
        $c->destroy();
        $secondId = $c->id();
        $this->assertNotSame($firstId, $secondId);
        $this->assertSame(
            [
                sprintf('Set-Cookie: PHPSESSID=%s; Secure; HttpOnly; SameSite=Strict', $secondId),
            ],
            $t->ls()
        );
    }
}
