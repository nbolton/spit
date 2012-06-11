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

class RelationType {
  const Generic = 0;
  const Duplicates = 1;
  const Blocks = 2;
  const Follows = 3;
}

class Relation {
  public $id;
  
  public function getHtmlInfo($linkProvder, $issueId) {
    if ($this->type == RelationType::Generic) {
      $format = T_("Related to: %s");
    }
    
    $issue = new \Spit\Models\Issue;
    if ($this->leftId == $issueId) {
      $issue->id = $this->rightId;
      $issue->title = $this->rightTitle;
      $issue->tracker = $this->rightTracker;
      $issue->closed = $this->rightClosed;
      
      switch ($this->type) {
        case RelationType::Duplicates: $format = T_("Duplicates: %s"); break;
        case RelationType::Blocks: $format = T_("Blocks: %s"); break;
        case RelationType::Follows: $format = T_("Follows: %s"); break;
      }
    }
    else {
      $issue->id = $this->leftId;
      $issue->title = $this->leftTitle;
      $issue->tracker = $this->leftTracker;
      $issue->closed = $this->leftClosed;
      
      switch ($this->type) {
        case RelationType::Duplicates: $format = T_("Duplicated by: %s"); break;
        case RelationType::Blocks: $format = T_("Blocked by: %s"); break;
        case RelationType::Follows: $format = T_("Followed by: %s"); break;
      }
    }    
    return sprintf($format, $issue->getHtmlInfo($linkProvder));
  }
}

?>
