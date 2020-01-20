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

use DateInterval;
use DateTime;
use fkooman\SeCookie\Exception\SessionException;
use RuntimeException;

class SessionStorage implements SessionStorageInterface
{
    /** @var string */
    const SESSION_FILE_PREFIX = 'sses_';

    /** @var \DateTime */
    protected $dateTime;

    /** @var string */
    private $sessionDir;

    /** @var array<string,resource> */
    private $lockedSessions = [];

    /**
     * @param string|null $sessionDir
     */
    public function __construct($sessionDir = null)
    {
        if (null === $sessionDir) {
            $sessionDir = \ini_get('session.save_path');
            if (false === $sessionDir || '' === $sessionDir) {
                $sessionDir = \sys_get_temp_dir();
            }
        }
        $this->sessionDir = $sessionDir;
        $this->dateTime = new DateTime();
    }

    /**
     * @return void
     */
    public function store(ActiveSession $activeSession)
    {
        $sessionId = $activeSession->sessionId();
        $filePointer = $this->getFilePointer($sessionId);
        $sessionDataString = \serialize($activeSession->sessionData());
        if (false === \fwrite($filePointer, $sessionDataString)) {
            throw new SessionException('unable to write session file');
        }
        if (false === \rewind($filePointer)) {
            throw new SessionException('unable to rewind session file');
        }
        $this->unlock($sessionId);
    }

    /**
     * @param string $sessionId
     *
     * @return void
     */
    public function create($sessionId)
    {
        $this->lock($sessionId);
    }

    /**
     * @param string $sessionId
     *
     * @return ActiveSession|null
     */
    public function retrieve($sessionId)
    {
        $sessionFile = $this->getSessionFileName($sessionId);
        if (false === \file_exists($sessionFile)) {
            // session doesn't exist (at all)
            return null;
        }
        $this->lock($sessionId);
        if (false === \file_exists($sessionFile)) {
            // we got the file lock, but session no longer exists, it was most
            // likely destroyed while we were waiting for the lock...
            $this->unlock($sessionId);

            return null;
        }
        $filePointer = $this->getFilePointer($sessionId);
        if (false === $sessionDataString = \fgets($filePointer)) {
            throw new SessionException('unable to read session file');
        }
        if (false === \rewind($filePointer)) {
            throw new SessionException('unable to rewind session file');
        }
        /** @var mixed */
        $sessionData = \unserialize($sessionDataString);
        if (!\is_array($sessionData)) {
            throw new SessionException('session data is corrupt');
        }

        return new ActiveSession($sessionId, $sessionData);
    }

    /**
     * @param string $sessionId
     *
     * @return void
     */
    public function destroy($sessionId)
    {
        $sessionFile = $this->getSessionFileName($sessionId);
        if (false === \unlink($sessionFile)) {
            throw new SessionException('unable to delete session data');
        }
        $this->unlock($sessionId);
    }

    /**
     * @return void
     */
    public function gc(DateInterval $expiresIn)
    {
        // loop over all session files and delete the session files that
        // haven't been modified for the amount of time specified in expiresIn
        if (false === $expiresAt = \date_sub(clone $this->dateTime, $expiresIn)) {
            throw new RuntimeException('unable to determine "expiresAt"');
        }
        $sessionFileFilter = \sprintf('%s/%s*', $this->sessionDir, self::SESSION_FILE_PREFIX);
        foreach (\glob($sessionFileFilter) as $sessionFile) {
            $lastModified = \filemtime($sessionFile);
            if ($lastModified < $expiresAt->getTimestamp()) {
                \unlink($sessionFile);
            }
        }
    }

    /**
     * @param string $sessionId
     *
     * @return resource
     */
    private function getFilePointer($sessionId)
    {
        if (!\array_key_exists($sessionId, $this->lockedSessions)) {
            throw new SessionException('session file not locked');
        }

        return $this->lockedSessions[$sessionId];
    }

    /**
     * @param string $sessionId
     *
     * @return void
     */
    private function lock($sessionId)
    {
        if (\array_key_exists($sessionId, $this->lockedSessions)) {
            throw new SessionException('session file already locked');
        }
        $sessionFile = $this->getSessionFileName($sessionId);
        if (false === $filePointer = \fopen($sessionFile, 'c+')) {
            throw new SessionException('unable to open session file');
        }
        if (false === \chmod($sessionFile, 0600)) {
            throw new SessionException('unable to set session file permission');
        }
        if (false === \flock($filePointer, LOCK_EX)) {
            throw new SessionException('unable to lock session file');
        }

        $this->lockedSessions[$sessionId] = $filePointer;
    }

    /**
     * @param string $sessionId
     *
     * @return void
     */
    private function unlock($sessionId)
    {
        $filePointer = $this->getFilePointer($sessionId);
        if (false === \fflush($filePointer)) {
            throw new SessionException('unable to flush data to session file');
        }
        // file has to be unlocked before it can be closed...
        if (false === \flock($filePointer, LOCK_UN)) {
            throw new SessionException('unable to unlock session file');
        }
        if (false === \fclose($filePointer)) {
            throw new SessionException('unable to close session file');
        }
        unset($this->lockedSessions[$sessionId]);
    }

    /**
     * @param string $sessionId
     *
     * @return string
     */
    private function getSessionFileName($sessionId)
    {
        return \sprintf('%s/%s%s', $this->sessionDir, self::SESSION_FILE_PREFIX, $sessionId);
    }
}
