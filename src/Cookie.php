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

class Cookie implements CookieInterface
{
    /** @var array */
    private $cookieOptions;

    /** @var HeaderInterface */
    private $header;

    /**
     * @param array                $cookieOptions
     * @param HeaderInterface|null $header
     */
    public function __construct(array $cookieOptions = [], HeaderInterface $header = null)
    {
        $this->cookieOptions = array_merge(
            [
                'Secure' => true,       // bool
                'HttpOnly' => true,     // bool
                'Path' => null,         // string
                'Domain' => null,       // string
                'Max-Age' => null,      // int > 0
                'SameSite' => 'Strict', // "Strict|Lax"
            ],
            $cookieOptions
        );
        if (is_null($header)) {
            $header = new PhpHeader();
        }
        $this->header = $header;
    }

    /**
     * Delete a cookie.
     *
     * @param string $name
     */
    public function delete($name)
    {
        self::set($name, '');
    }

    /**
     * Set a cookie value.
     *
     * @param string $name  the cookie name
     * @param string $value the cookie value
     */
    public function set($name, $value)
    {
        $attributeValueList = [];

        if ($this->cookieOptions['Secure']) {
            $attributeValueList[] = 'Secure';
        }
        if ($this->cookieOptions['HttpOnly']) {
            $attributeValueList[] = 'HttpOnly';
        }

        if (!is_null($this->cookieOptions['Path'])) {
            $attributeValueList[] = sprintf('Path=%s', $this->cookieOptions['Path']);
        }
        if (!is_null($this->cookieOptions['Domain'])) {
            $attributeValueList[] = sprintf('Domain=%s', $this->cookieOptions['Domain']);
        }
        if (!is_null($this->cookieOptions['Max-Age'])) {
            $attributeValueList[] = sprintf('Max-Age=%d', $this->cookieOptions['Max-Age']);
        }

        if (!is_null($this->cookieOptions['SameSite'])) {
            $attributeValueList[] = sprintf('SameSite=%s', $this->cookieOptions['SameSite']);
        }

        $this->header->set(
            sprintf(
                'Set-Cookie: %s=%s; %s',
                $name,
                $value,
                implode('; ', $attributeValueList)
            ),
            false // do not replace
        );
    }

    /**
     * Replace an existing cookie.
     *
     * @param string $name  the cookie name
     * @param string $value the cookie value
     */
    protected function replace($name, $value)
    {
        $cookieList = [];
        foreach ($this->header->ls() as $hdr) {
            if (0 === stripos($hdr, 'Set-Cookie: ')) {
                // found "Set-Cookie"
                if (0 !== stripos($hdr, sprintf('Set-Cookie: %s=', $name))) {
                    // not the one we want to replace, add to backup list
                    $cookieList[] = $hdr;
                }
            }
        }
        // remove all "Set-Cookie" headers, `header_remove()` is case
        // insensitive
        $this->header->remove('Set-Cookie');

        // restore cookies we want to keep
        foreach ($cookieList as $cookie) {
            $this->header->set($cookie, false);
        }

        self::set($name, $value);
    }
}
