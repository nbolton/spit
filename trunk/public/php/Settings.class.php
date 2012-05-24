<?php

namespace Spit;

class SettingsSection { }

class Settings {
  
  public function __construct() {
    $ini = parse_ini_file("settings.ini", true);
    foreach ($ini as $section => $values) {
      $this->$section = new SettingsSection;
      foreach ($values as $k => $v) {
        $this->$section->$k = $v;
      }
    }
  }
}

?>
