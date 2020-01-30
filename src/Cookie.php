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

class Cookie
{
    /** @var string */
    const NO_SAME_SITE_POSTFIX = '_NOSS';

    /** @var CookieOptions */
    private $cookieOptions;

    public function __construct(CookieOptions $cookieOptions = null)
    {
        if (null === $cookieOptions) {
            $cookieOptions = new CookieOptions();
        }
        $this->cookieOptions = $cookieOptions;
    }

    /**
     * @param string $cookieName
     *
     * @return void
     */
    public function delete($cookieName)
    {
        $this->set($cookieName, '');
    }

    /**
     * @param string $cookieName
     * @param string $cookieValue
     *
     * @return void
     */
    public function set($cookieName, $cookieValue)
    {
        $this->sendHeader(
            \sprintf(
                'Set-Cookie: %s=%s; %s',
                $cookieName,
                $cookieValue,
                \implode('; ', $this->cookieOptions->attributeValueList('' === $cookieValue))
            )
        );

        // after chrome moves to SameSite=Lax by default, we have to explicitly
        // specify SameSite=None if we do NOT want Lax behavior. However, this
        // breaks some old(er) browsers as they interprete SameSite=None as
        // SameSite=Strict. For those we have to send a version without any
        // SameSite attribute.
        //
        // The approach that does *not* involve browser user agent sniffing
        // requires sending two cookies, one with SameSite=None and one without
        // any SameSite attribute...
        //
        // @see https://www.chromium.org/updates/same-site
        // @see https://web.dev/samesite-cookie-recipes/#handling-incompatible-clients
        if ('None' === $this->cookieOptions->getSameSite()) {
            $cookieOptions = clone $this->cookieOptions;
            $cookieOptions->setSameSite(null);
            $this->sendHeader(
                \sprintf(
                    'Set-Cookie: %s%s=%s; %s',
                    $cookieName,
                    self::NO_SAME_SITE_POSTFIX,
                    $cookieValue,
                    \implode('; ', $cookieOptions->attributeValueList('' === $cookieValue))
                )
            );
        }
    }

    /**
     * @param string $cookieName
     *
     * @return string|null
     */
    public function get($cookieName)
    {
        if (null === $cookieValue = $this->readCookie($cookieName)) {
            return $this->readCookie($cookieName.self::NO_SAME_SITE_POSTFIX);
        }

        return $cookieValue;
    }

    /**
     * @param string $headerKeyValue
     *
     * @return void
     */
    protected function sendHeader($headerKeyValue)
    {
        // keep existing headers with same name
        \header($headerKeyValue, false);
    }

    /**
     * @param string $cookieName
     *
     * @return string|null
     */
    protected function readCookie($cookieName)
    {
        if (!\array_key_exists($cookieName, $_COOKIE)) {
            return null;
        }
        /** @var string|array<string> */
        $cookieValue = $_COOKIE[$cookieName];
        if (!\is_string($cookieValue)) {
            return null;
        }

        return $cookieValue;
    }
}
