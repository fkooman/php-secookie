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

use fkooman\SeCookie\Exception\CookieException;

class CookieOptions
{
    /** @var bool */
    private $secure = true;

    /** @var string|null */
    private $path = null;

    /** @var int|null */
    private $maxAge = null;

    /** @var string|null */
    private $sameSite = 'Lax';

    /**
     * @return self
     */
    public static function init()
    {
        return new self();
    }

    /**
     * @param bool $secure
     *
     * @return self
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param int $maxAge
     *
     * @return self
     */
    public function setMaxAge($maxAge)
    {
        if (0 >= $maxAge) {
            throw new CookieException('"MaxAge" must be positive');
        }
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * @param string|null $sameSite
     *
     * @return self
     */
    public function setSameSite($sameSite)
    {
        if (null !== $sameSite) {
            if (!\in_array($sameSite, ['Strict', 'Lax', 'None'], true)) {
                throw new CookieException(\sprintf('"%s" is not a supported value for "SameSite"', $sameSite));
            }
        }
        $this->sameSite = $sameSite;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * @param bool $deleteCookie
     *
     * @return array<string>
     */
    public function attributeValueList($deleteCookie)
    {
        $attributeValueList = [
            'HttpOnly',  // all cookies are ALWAYS "HttpOnly"
        ];
        if ($this->secure) {
            $attributeValueList[] = 'Secure';
        }
        if (null !== $path = $this->path) {
            $attributeValueList[] = \sprintf('Path=%s', $path);
        }
        if (null !== $sameSite = $this->sameSite) {
            $attributeValueList[] = \sprintf('SameSite=%s', $sameSite);
        }
        if (null !== $maxAge = $this->determineMaxAge($deleteCookie)) {
            $attributeValueList[] = \sprintf('Max-Age=%d', $maxAge);
        }

        \sort($attributeValueList, SORT_STRING);

        return $attributeValueList;
    }

    /**
     * @param bool $deleteCookie
     *
     * @return int|null
     */
    private function determineMaxAge($deleteCookie)
    {
        if ($deleteCookie) {
            return 0;
        }

        return $this->maxAge;
    }
}
