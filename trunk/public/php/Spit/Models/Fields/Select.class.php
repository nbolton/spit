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

namespace Spit\Models\Fields;

require_once "Field.class.php";
require_once "SelectOption.class.php";

class Select extends Field {
  
  public $options = array();
  public $optionIndex = 1;
  
  public function __construct($label, $name) {
    parent::__construct($label, $name);
    $this->type = "select";
  }
  
  public function add($name) {
    array_push($this->options, new SelectOption($this->optionIndex++, $name));
  }
}

?>
