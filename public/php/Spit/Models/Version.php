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

class Version {
  public $id;
  
  public function getProgress() {
    return $this->complete / count($this->issues);
  }
  
  public function getProgressPercent() {
    return round($this->getProgress() * 100, 0);
  }
  
  public function getProgressBarWidth($max) {
    return $max * $this->getProgress();
  }
  
  public function getDateInfo($dateFormatter) {
    $now = new \DateTime();
    $diff = $this->releaseDate->diff($now);
    $days = $diff->days;
    
    // it would be nice to use T_ngettext here, but poedit doesn't
    // seem to support it even with the keyword "T_ngettext:1,2"
    if ($days == 0) {
      if ($this->released) {
        $format = T_("Released on %s (today)");
      }
      else {
        $format = T_("Due on %s (today)");
      }
    }
    else if ($diff->invert) {
      if ($this->released) {
        $s = T_("Released on %s (in %d day)");
        $p = T_("Released on %s (in %d days)");
      }
      else {
        $s = T_("Due on %s (in %d day)");
        $p = T_("Due on %s (in %d days)");
      }
      $format = $days == 1 ? $s : $p;
    }
    else {
      if ($this->released) {
        $s = T_("Released on %s (%d day ago)");
        $p = T_("Released on %s (%d days ago)");
      }
      else {
        $s = T_("Due on %s (%d day ago)");
        $p = T_("Due on %s (%d days ago)");
      }
      $format = $days == 1 ? $s : $p;
    }
    
    return sprintf(
      $format, $dateFormatter->format($this->releaseDate), $days);
  }
}

?>
