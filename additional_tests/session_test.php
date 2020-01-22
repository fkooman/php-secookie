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

require \dirname(__DIR__).'/vendor/autoload.php';

use fkooman\SeCookie\Session;

$session = new Session();
$session->start();

$a = \array_key_exists('a', $_GET) ? $_GET['a'] : '1';

switch ($a) {
    case '1':
        $session->set('foo', 'bar');
        break;
    case '2':
        \sleep(2);
        $session->destroy();
        break;
    case '3':
        echo $session->get('foo');
        break;
    case '4':
        // NOP
        break;
    case '5':
        if (null === $counter = $session->get('counter')) {
            $counter = 0;
        }
        $counter = (int) $counter;
        ++$counter;
        $session->set('counter', (string) $counter);
        echo $counter;
        break;
}
