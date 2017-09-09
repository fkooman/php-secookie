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

namespace fkooman\SeCookie;

interface SessionInterface
{
    /**
     * Get the session ID.
     *
     * @return string
     */
    public function id();

    /**
     * Regenerate the session ID.
     *
     * @param bool $deleteOldSession
     *
     * @return void
     */
    public function regenerate($deleteOldSession = false);

    /**
     * Set session value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set($key, $value);

    /**
     * Delete session key/value.
     *
     * @param string $key
     *
     * @return void
     */
    public function delete($key);

    /**
     * Test if session key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Get session value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Empty the session.
     *
     * @return void
     */
    public function destroy();
}
