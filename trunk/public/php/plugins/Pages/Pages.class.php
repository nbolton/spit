<?php

require "DownloadController.class.php";

class Pages {
  
  public function __construct($spit) {
    $spit->addLink(new Spit\Link("Download", "download"));
    $spit->addController("download", new DownloadController($this));
  }
}

?>
