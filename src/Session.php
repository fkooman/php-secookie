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

use DateTime;
use fkooman\SeCookie\Exception\SessionException;

class Session
{
    /** @var SessionStorageInterface */
    protected $sessionStorage;

    /** @var \DateTime */
    protected $dateTime;

    /** @var Cookie */
    protected $cookie;

    /** @var SessionOptions */
    private $sessionOptions;

    /** @var ActiveSession|null */
    private $activeSession = null;

    public function __construct(SessionOptions $sessionOptions = null, CookieOptions $cookieOptions = null)
    {
        if (null === $sessionOptions) {
            $sessionOptions = new SessionOptions();
        }
        $this->sessionOptions = $sessionOptions;
        if (null === $cookieOptions) {
            $cookieOptions = new CookieOptions();
        }
        $this->cookie = new Cookie($cookieOptions);
        $this->sessionStorage = new SessionStorage();
        $this->dateTime = new DateTime();
    }

    public function __destruct()
    {
        // stopping by destructor does not require there to be an active
        // session, maybe it was never started...
        if (null !== $activeSession = $this->activeSession) {
            $this->sessionStorage->store($activeSession);
            $this->activeSession = null;
        }
    }

    /**
     * @return void
     */
    public function start()
    {
        if (null !== $this->activeSession) {
            // if session is already active, do nothing
            return;
        }

        // we take the exact same values PHP 7.3 also provides (by default)
        // after session_start()
        $this->sendHeader('Cache-Control: no-store, no-cache, must-revalidate');
        $this->sendHeader('Pragma: no-cache');

        $sessionName = $this->sessionOptions->getName();
        if (null === $sessionId = $this->cookie->get($sessionName)) {
            // no session cookie received
            $this->createSession();

            return;
        }

        if (!self::isValidSessionId($sessionId)) {
            // invalid session ID (syntax) provided
            $this->createSession();

            return;
        }

        if (null === $activeSession = $this->sessionStorage->retrieve($sessionId)) {
            // no active session found
            $this->createSession();

            return;
        }

        if ($activeSession->isExpired($this->dateTime)) {
            // session expired
            $this->sessionStorage->destroy($activeSession->sessionId());
            $this->createSession();

            return;
        }

        // we have a valid session
        $this->activeSession = $activeSession;
    }

    /**
     * @return void
     */
    public function stop()
    {
        $activeSession = $this->requireActiveSession();
        $this->sessionStorage->store($activeSession);
        $this->activeSession = null;
    }

    /**
     * @return void
     */
    public function regenerate()
    {
        $activeSession = $this->requireActiveSession();
        $this->destroy();
        // use current data for new session
        $this->createSession($activeSession->sessionData());
    }

    /**
     * @return void
     */
    public function destroy()
    {
        $activeSession = $this->requireActiveSession();
        $this->sessionStorage->destroy($activeSession->sessionId());
        $this->activeSession = null;
    }

    /**
     * @param string $sessionKey
     * @param string $sessionValue
     *
     * @return void
     */
    public function set($sessionKey, $sessionValue)
    {
        $activeSession = $this->requireActiveSession();
        $activeSession->set($sessionKey, $sessionValue);
    }

    /**
     * @param string $sessionKey
     *
     * @return string|null
     */
    public function get($sessionKey)
    {
        $activeSession = $this->requireActiveSession();

        return $activeSession->get($sessionKey);
    }

    /**
     * @param string $sessionKey
     *
     * @return void
     */
    public function remove($sessionKey)
    {
        $activeSession = $this->requireActiveSession();
        $activeSession->remove($sessionKey);
    }

    /**
     * @return string
     */
    protected function getRandomBytes()
    {
        return \random_bytes(32);
    }

    /**
     * @param string $headerKeyValue
     *
     * @return void
     */
    protected function sendHeader($headerKeyValue)
    {
        // overwrite existing headers with same name
        \header($headerKeyValue, true);
    }

    /**
     * @return ActiveSession
     */
    private function requireActiveSession()
    {
        if (null === $activeSession = $this->activeSession) {
            throw new SessionException('session not active');
        }

        return $activeSession;
    }

    /**
     * @return void
     */
    private function createSession(array $sessionData = [])
    {
        $sessionName = $this->sessionOptions->getName();
        $sessionId = Encoding::bin2hex($this->getRandomBytes());
        $activeSession = new ActiveSession($sessionId, $sessionData);
        // override/set the expiry of the session
        $activeSession->set('__expires_at', $this->calculateExpiresAt());
        $this->sessionStorage->create($sessionId);
        $this->activeSession = $activeSession;
        $this->cookie->set($sessionName, $sessionId);
    }

    /**
     * @param string $sessionId
     *
     * @return bool
     */
    private static function isValidSessionId($sessionId)
    {
        return 1 === \preg_match('/^[0-9a-f]{64}$/', $sessionId);
    }

    /**
     * @return string
     */
    private function calculateExpiresAt()
    {
        $expiresAt = clone $this->dateTime;
        $expiresAt->add($this->sessionOptions->getExpiresIn());

        return $expiresAt->format(DateTime::ATOM);
    }
}
