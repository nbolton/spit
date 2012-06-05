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

namespace Spit;

class LinkProvider {
  public $app;

  public function __construct($app) {
    $this->app = $app;
  }
  
  public function forIssue($id) {
    return sprintf("%s/issues/details/%d/", $this->app->getProjectRoot(false), $id);
  }
  
  public function forAttachment($attachment) {
    return sprintf("%s/attachments/%s", $this->app->getProjectRoot(false), $attachment->physicalName);
  }
}

?>
