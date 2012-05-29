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

namespace Spit\Models;

class ChangeType {
  const Edit = 0;
  const Comment = 1;
}

class Change {
  public $id;
  
  public function getContentHtml() {
    switch($this->type) {
      case ChangeType::Edit:
        return str_replace("\n", "<br />\n", $this->content);
      
      case ChangeType::Comment:
        return Markdown($this->content);
      
      default: return null;
    }
  }
  
  public function getTypeString() {
    switch($this->type) {
      case ChangeType::Edit: return T_("Edit");
      case ChangeType::Comment: return T_("Comment");
      default: return null;
    }
  }
  
  public function getDateString() {
    return $this->created->format(
      \Spit\Settings::$instance->site->dateTimeFormat);
  }
}

?>
