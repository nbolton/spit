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

namespace Spit\Controllers;

class ErrorController extends Controller {
  
  public function __construct($app) {
    $this->app = $app;
  }

  public function show($code) {
    $title = "";
    switch ($code) {
      case 404: $title = T_("Not Found"); break;
    }
    
    header(sprintf("HTTP/1.0 %d %s", $code, $title));
    $this->showView("error/" . $code, sprintf("%d %s", $code, $title));
  }
}

?>
