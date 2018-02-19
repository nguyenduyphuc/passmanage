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

namespace db;

//! In-memory/flat-file DB wrapper
class jig
{
    //@{ Storage formats
    const
        FORMAT_JSON = 0,
        FORMAT_Serialized = 1;
    //@}

    //! UUID
        protected $uuid;
    protected //! Storage location
        $dir;
    protected //! Current storage format
        $format;
    protected //! Jig log
        $log;
    protected //! Memory-held data
        $data;

    /**
     *   Read data from memory/file.
     *
     *   @return array
     *
     *   @param $file string
     **/
    public function &read($file)
    {
        if (!$this->dir || !is_file($dst = $this->dir.$file)) {
            if (!isset($this->data[$file])) {
                $this->data[$file] = array();
            }

            return $this->data[$file];
        }
        $fw = \Base::instance();
        $raw = $fw->read($dst);
        switch ($this->format) {
            case self::FORMAT_JSON:
                $data = json_decode($raw, true);
                break;
            case self::FORMAT_Serialized:
                $data = $fw->unserialize($raw);
                break;
        }
        $this->data[$file] = $data;

        return $this->data[$file];
    }

    /**
     *   Write data to memory/file.
     *
     *   @return int
     *
     *   @param $file string
     *   @param $data array
     **/
    public function write($file, array $data = null)
    {
        if (!$this->dir) {
            return count($this->data[$file] = $data);
        }
        $fw = \Base::instance();
        switch ($this->format) {
            case self::FORMAT_JSON:
                $out = json_encode($data, @constant('JSON_PRETTY_PRINT'));
                break;
            case self::FORMAT_Serialized:
                $out = $fw->serialize($data);
                break;
        }

        return $fw->write($this->dir.'/'.$file, $out);
    }

    /**
     *   Return directory.
     *
     *   @return string
     **/
    public function dir()
    {
        return $this->dir;
    }

    /**
     *   Return UUID.
     *
     *   @return string
     **/
    public function uuid()
    {
        return $this->uuid;
    }

    /**
     *   Return profiler results.
     *
     *   @return string
     **/
    public function log()
    {
        return $this->log;
    }

    /**
     *   Jot down log entry.
     *
     *   @return NULL
     *
     *   @param $frame string
     **/
    public function jot($frame)
    {
        if ($frame) {
            $this->log .= date('r').' '.$frame.PHP_EOL;
        }
    }

    /**
     *   Clean storage.
     *
     *   @return NULL
     **/
    public function drop()
    {
        if (!$this->dir) {
            $this->data = array();
        } elseif ($glob = @glob($this->dir.'/*', GLOB_NOSORT)) {
            foreach ($glob as $file) {
                @unlink($file);
            }
        }
    }

    /**
     *   Instantiate class.
     *
     *   @param $dir string
     *   @param $format int
     **/
    public function __construct($dir = null, $format = self::FORMAT_JSON)
    {
        if ($dir && !is_dir($dir)) {
            mkdir($dir, \Base::MODE, true);
        }
        $this->uuid = \Base::instance()->hash($this->dir = $dir);
        $this->format = $format;
    }
}
