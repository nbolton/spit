<?php

/*
 * SPIT: Simple PHP Issue Tracker
 * Copyright (C) 2012 Nick Bolton
 * 
 * This package is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * found in the file COPYING that should have accompanied this file.
 * 
 * This package is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require "DownloadController.class.php";
require "ContactController.class.php";

class Pages {
  
  public function __construct($spit) {
    $spit->addLink(new Spit\Link(T_("Download"), "download/", \Spit\LinkType::Site));
    $spit->addLink(new Spit\Link(T_("Contact"), "contact/", \Spit\LinkType::Site));
    $spit->addLink(new Spit\Link(T_("Code"), "/code/", \Spit\LinkType::External));
    $spit->addLink(new Spit\Link(T_("Wiki"), "/wiki/", \Spit\LinkType::External));
    
    $spit->addController("download", new DownloadController($this));
    $spit->addController("contact", new ContactController($this));
  }
}

?>
