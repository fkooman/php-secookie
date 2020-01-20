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
use fkooman\SeCookie\Exception\SessionException;
use fkooman\SeCookie\SessionStorage;

class TestSessionStorage extends SessionStorage
{
    /** @var string */
    private $tmpDir;

    /**
     * @param array<string,array<string,string>> $sessionList
     */
    public function __construct(array $sessionList)
    {
        $this->tmpDir = \sprintf('%s/php-secookie-test-%s', \sys_get_temp_dir(), \bin2hex(\random_bytes(32)));
        \mkdir($this->tmpDir);
        parent::__construct($this->tmpDir);
        $this->dateTime = new DateTime('2019-01-01 08:00:00+00:00');

        // write the sessionData to the sessionDir
        foreach ($sessionList as $sessionId => $sessionData) {
            $sessionFile = \sprintf('%s/%s%s', $this->tmpDir, self::SESSION_FILE_PREFIX, $sessionId);
            \file_put_contents($sessionFile, \serialize($sessionData));
            // set filemtime for this special session file to test GC
            // this one should NOT be cleared!
            if ($sessionFile === \sprintf('%s/%s7878787878787878787878787878787878787878787878787878787878787878', $this->tmpDir, self::SESSION_FILE_PREFIX)) {
                \touch($sessionFile, (int) ($this->dateTime->getTimestamp() - 4 * 60 * 60));
            }
            // this one should be cleared!
            if ($sessionFile === \sprintf('%s/%s8989898989898989898989898989898989898989898989898989898989898989', $this->tmpDir, self::SESSION_FILE_PREFIX)) {
                \touch($sessionFile, (int) ($this->dateTime->getTimestamp() - 10 * 60 * 60));
            }
        }
    }

    /**
     * @return array<string,array<string,string>>
     */
    public function getAll()
    {
        $sessionList = [];
        foreach (\glob(\sprintf('%s/%s*', $this->tmpDir, self::SESSION_FILE_PREFIX)) as $sessionFile) {
            $sessionId = \substr(\basename($sessionFile), \strlen(self::SESSION_FILE_PREFIX));
            if (false === $sessionDataString = \file_get_contents($sessionFile)) {
                throw new SessionException('unable to read session file');
            }
            /** @var mixed */
            $sessionData = \unserialize($sessionDataString);
            if (!\is_array($sessionData)) {
                throw new SessionException('session data is corrupt');
            }
            $sessionList[$sessionId] = $sessionData;
        }

        return $sessionList;
    }
}
