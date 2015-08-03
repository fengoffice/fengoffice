<?php

  /**
  * Container interface
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  interface IContainer {
    public function has($var);
    public function get($var, $default = null);
    public function set($var, $value);
    public function drop($var);
    public function clear();
    public function import($data);
    public function export();
  } // IContainer

?>