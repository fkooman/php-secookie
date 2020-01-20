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

use DateInterval;
use PHPUnit\Framework\TestCase;

class SessionStorageTest extends TestCase
{
    /**
     * @return void
     */
    public function testGarbageCollection()
    {
        // we know there is an expired session with a "special" SID, the
        // 8989898989898989898989898989898989898989898989898989898989898989 one
        $sessionStorage = new TestSessionStorage(
            [
                '7878787878787878787878787878787878787878787878787878787878787878' => [
                    '__expires_at' => '2019-01-01T16:00:00+00:00',
                    'foo' => 'bar',
                ],
                '8989898989898989898989898989898989898989898989898989898989898989' => [
                    '__expires_at' => '2019-01-01T16:00:00+00:00',
                    'foo' => 'bar',
                ],
            ]
        );
        $this->assertSame(
            [
                '7878787878787878787878787878787878787878787878787878787878787878' => [
                    '__expires_at' => '2019-01-01T16:00:00+00:00',
                    'foo' => 'bar',
                ],
                '8989898989898989898989898989898989898989898989898989898989898989' => [
                    '__expires_at' => '2019-01-01T16:00:00+00:00',
                    'foo' => 'bar',
                ],
            ],
            $sessionStorage->getAll()
        );

        // run the GC
        $sessionStorage->gc(new DateInterval('PT8H'));

        // the "special session" should be gone now!
        $this->assertSame(
            [
                '7878787878787878787878787878787878787878787878787878787878787878' => [
                    '__expires_at' => '2019-01-01T16:00:00+00:00',
                    'foo' => 'bar',
                ],
            ],
            $sessionStorage->getAll()
        );
    }
}
