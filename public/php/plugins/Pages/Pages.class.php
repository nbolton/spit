<?php

require "DownloadController.class.php";

class Pages {
  
  public function __construct($spit) {
    $spit->addLink(new Spit\Link("Download", "download/"));
    $spit->addLink(new Spit\Link("Code", "/code/", true));
    $spit->addLink(new Spit\Link("Wiki", "/wiki/", true));
    $spit->addController("download", new DownloadController($this));
  }
}

?>
