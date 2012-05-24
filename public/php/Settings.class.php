<?php

namespace Spit;

class Settings {
  
  public function __construct() {
    $ini = parse_ini_file("settings.ini");
    foreach ($ini as $k => $v) {
      $this->$k = $v;
    }
  }
}

?>
