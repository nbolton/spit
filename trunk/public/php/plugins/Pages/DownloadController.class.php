<?php

class DownloadController extends Spit\Controllers\Controller {
  
  public function __construct() {
    $this->viewDir = "php/plugins/Pages/views/";
  }
  
  public function run() {
    $this->showView("download");
  }
}

?>
