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
require "ThemesController.class.php";
require "PluginsController.class.php";

class Pages {
  
  public function __construct($spit) {
    $spit->addLink(new Spit\Link(T_("Download"), "download/"));
    $spit->addLink(new Spit\Link(T_("Themes"), "themes/"));
    $spit->addLink(new Spit\Link(T_("Plugins"), "plugins/"));
    $spit->addLink(new Spit\Link(T_("Code"), "/code/", true));
    $spit->addLink(new Spit\Link(T_("Wiki"), "/wiki/", true));
    
    $spit->addController("download", new DownloadController($this));
    $spit->addController("themes", new ThemesController($this));
    $spit->addController("plugins", new PluginsController($this));
  }
}

?>
