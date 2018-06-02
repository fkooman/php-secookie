<?php

/*
 * Copyright (c) 2017, 2018 François Kooman <fkooman@tuxed.net>
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

use DateInterval;
use DateTime;
use fkooman\SeCookie\Exception\SessionException;

class Session implements SessionInterface
{
    /** @var array */
    private $sessionOptions;

    /** @var Cookie */
    private $cookie;

    /**
     * @param array       $sessionOptions
     * @param Cookie|null $cookie
     */
    public function __construct(array $sessionOptions = [], Cookie $cookie = null)
    {
        $this->sessionOptions = \array_merge(
            [
                'SessionExpiry' => 'PT08H', // expire session (8 hours)
                'CanaryExpiry' => 'PT01H',  // regenerate session ID (1 hour)
                'DomainBinding' => null,    // also bind session to Domain
                'PathBinding' => null,      // also bind session to Path
                'SessionName' => null,      // override the default session name
            ],
            $sessionOptions
        );

        if (null === $cookie) {
            $cookie = new Cookie();
        }
        $this->cookie = $cookie;

        if (null !== $this->sessionOptions['SessionName']) {
            \session_name($this->sessionOptions['SessionName']);
        }

        if (PHP_SESSION_ACTIVE !== \session_status()) {
            \session_start();
        }

        $this->sessionCanary();
        $this->domainBinding();
        $this->pathBinding();
        $this->sessionExpiry();

        $this->cookie->replace(\session_name(), \session_id());
    }

    /**
     * {@inheritdoc}
     */
    public function id()
    {
        return \session_id();
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate($deleteOldSession = false)
    {
        \session_regenerate_id($deleteOldSession);
        $this->cookie->replace(\session_name(), \session_id());
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return \array_key_exists($key, $_SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new SessionException(\sprintf('key "%s" not available in session', $key));
        }

        return $_SESSION[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        $_SESSION = [];
        $this->regenerate(true);
    }

    /**
     * @return void
     */
    private function sessionCanary()
    {
        $dateTime = new DateTime();
        if (!\array_key_exists('Canary', $_SESSION) || !\array_key_exists('Expiry', $_SESSION)) {
            $_SESSION = [];
            $this->regenerate(true);
            $_SESSION['Canary'] = $dateTime->format('Y-m-d H:i:s');
            $_SESSION['Expiry'] = $dateTime->format('Y-m-d H:i:s');
        } else {
            $canaryDateTime = new DateTime($_SESSION['Canary']);
            $canaryDateTime->add(new DateInterval($this->sessionOptions['CanaryExpiry']));
            if ($canaryDateTime < $dateTime) {
                $this->regenerate(true);
                $_SESSION['Canary'] = $dateTime->format('Y-m-d H:i:s');
            }
        }
    }

    /**
     * @return void
     */
    private function domainBinding()
    {
        $this->sessionBinding('DomainBinding');
    }

    /**
     * @return void
     */
    private function pathBinding()
    {
        $this->sessionBinding('PathBinding');
    }

    /**
     * @param string $key
     *
     * @return void
     */
    private function sessionBinding($key)
    {
        if (null !== $this->sessionOptions[$key]) {
            if (!\array_key_exists($key, $_SESSION)) {
                $_SESSION[$key] = $this->sessionOptions[$key];
            }
            if ($this->sessionOptions[$key] !== $_SESSION[$key]) {
                throw new SessionException(\sprintf('session bound to %s, we got "%s", but expected "%s"', $key, $_SESSION[$key], $this->sessionOptions[$key]));
            }
        }
    }

    /**
     * Expire session after a specified time.
     *
     * @return void
     */
    private function sessionExpiry()
    {
        $dateTime = new DateTime();
        if (null !== $this->sessionOptions['SessionExpiry']) {
            $expiryDateTime = new DateTime($_SESSION['Expiry']);
            $expiryDateTime->add(new DateInterval($this->sessionOptions['SessionExpiry']));
            if ($expiryDateTime < $dateTime) {
                $this->destroy();
            }
        }
    }
}
