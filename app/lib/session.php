<?php

/*

    Copyright (c) 2009-2015 F3::Factory/Bong Cosca, All rights reserved.

    This file is part of the Fat-Free Framework (http://fatfreeframework.com).

    This is free software: you can redistribute it and/or modify it under the
    terms of the GNU General Public License as published by the Free Software
    Foundation, either version 3 of the License, or later.

    Fat-Free Framework is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with Fat-Free Framework.  If not, see <http://www.gnu.org/licenses/>.

*/

//! Cache-based session handler
class Session
{
    //! Session ID
        protected $sid;

    /**
     *   Open session.
     *
     *   @return TRUE
     *
     *   @param $path string
     *   @param $name string
     **/
    public function open($path, $name)
    {
        return true;
    }

    /**
     *   Close session.
     *
     *   @return TRUE
     **/
    public function close()
    {
        return true;
    }

    /**
     *   Return session data in serialized format.
     *
     *   @return string|FALSE
     *
     *   @param $id string
     **/
    public function read($id)
    {
        if ($id != $this->sid) {
            $this->sid = $id;
        }

        return Cache::instance()->exists($id.'.@', $data) ? $data['data'] : false;
    }

    /**
     *   Write session data.
     *
     *   @return TRUE
     *
     *   @param $id string
     *   @param $data string
     **/
    public function write($id, $data)
    {
        $fw = \Base::instance();
        $sent = headers_sent();
        $headers = $fw->get('HEADERS');
        $csrf = $fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
            $fw->hash(mt_rand());
        $jar = $fw->get('JAR');
        if ($id != $this->sid) {
            $this->sid = $id;
        }
        \Cache::instance()->set($id.'.@',
            array(
                'data' => $data,
                'csrf' => $sent ? $this->csrf() : $csrf,
                'ip' => $fw->get('IP'),
                'agent' => isset($headers['User-Agent']) ?
                    $headers['User-Agent'] : '',
                'stamp' => time(),
            ),
            $jar['expire'] ? ($jar['expire'] - time()) : 0
        );

        return true;
    }

    /**
     *   Destroy session.
     *
     *   @return TRUE
     *
     *   @param $id string
     **/
    public function destroy($id)
    {
        \Cache::instance()->clear($id.'.@');
        setcookie(session_name(), '', strtotime('-1 year'));
        unset($_COOKIE[session_name()]);
        header_remove('Set-Cookie');

        return true;
    }

    /**
     *   Garbage collector.
     *
     *   @return TRUE
     *
     *   @param $max int
     **/
    public function cleanup($max)
    {
        \Cache::instance()->reset('.@', $max);

        return true;
    }

    /**
     *   Return anti-CSRF token.
     *
     *   @return string|FALSE
     **/
    public function csrf()
    {
        return \Cache::instance()->
            exists(($this->sid ?: session_id()).'.@', $data) ?
                $data['csrf'] : false;
    }

    /**
     *   Return IP address.
     *
     *   @return string|FALSE
     **/
    public function ip()
    {
        return \Cache::instance()->
            exists(($this->sid ?: session_id()).'.@', $data) ?
                $data['ip'] : false;
    }

    /**
     *   Return Unix timestamp.
     *
     *   @return string|FALSE
     **/
    public function stamp()
    {
        return \Cache::instance()->
            exists(($this->sid ?: session_id()).'.@', $data) ?
                $data['stamp'] : false;
    }

    /**
     *   Return HTTP user agent.
     *
     *   @return string|FALSE
     **/
    public function agent()
    {
        return \Cache::instance()->
            exists(($this->sid ?: session_id()).'.@', $data) ?
                $data['agent'] : false;
    }

    /**
     *   Instantiate class.
     *
     *   @param $onsuspect callback
     **/
    public function __construct($onsuspect = null)
    {
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'cleanup')
        );
        register_shutdown_function('session_commit');
        @session_start();
        $fw = \Base::instance();
        $headers = $fw->get('HEADERS');
        if (($ip = $this->ip()) && $ip != $fw->get('IP') ||
            ($agent = $this->agent()) &&
            (!isset($headers['User-Agent']) ||
                $agent != $headers['User-Agent'])) {
            if (isset($onsuspect)) {
                $fw->call($onsuspect, array($this));
            } else {
                session_destroy();
                $fw->error(403);
            }
        }
        $csrf = $fw->hash($fw->get('ROOT').$fw->get('BASE')).'.'.
            $fw->hash(mt_rand());
        $jar = $fw->get('JAR');
        if (\Cache::instance()->exists(($this->sid = session_id()).'.@', $data)) {
            $data['csrf'] = $csrf;
            \Cache::instance()->set($this->sid.'.@',
                $data,
                $jar['expire'] ? ($jar['expire'] - time()) : 0
            );
        }
    }
}
