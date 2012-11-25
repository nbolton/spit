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

class Query {
  
  var $name;
  var $filter;
  var $order;
  
  function __construct() {
    $this->filters = array();
    $this->filters["tracker"] = "t.name";
    $this->filters["status"] = "s.name";
    $this->filters["priority"] = "p.name";
    
    $this->orders = array();
    $this->orders["activity"] = "activity";
    $this->orders["votes"] = "i.votes";
    $this->orders["tracker"] = "t.order";
    $this->orders["status"] = "s.order";
    $this->orders["priority"] = "p.order";
  }
  
  public function getFilterEncoded() {
    return htmlentities($this->filter, ENT_COMPAT, "UTF-8");
  }
  
  public function getOrderEncoded() {
    return htmlentities($this->order, ENT_COMPAT, "UTF-8");
  }
  
  public function getFilterSql($ds) {
    $expressions = preg_split("/and/", $this->filter, null, PREG_SPLIT_NO_EMPTY);
    
    $sql = null;
    foreach ($expressions as $expression) {
      $operands = preg_split("/=/", $expression);
      if (count($operands) != 2) {
        continue;
      }
      
      $left = trim($operands[0]);
      
      $rightMatches = array();
      if (!preg_match("/.*\"(.*)\".*/", $operands[1], $rightMatches)) {
        continue;
      }
    
      $right = $rightMatches[1];
      
      if (array_key_exists($left, $this->filters)) {
        $sql .= sprintf("and %s = %s ", $this->filters[$left], $ds->cleanArg($right));
      }
    }
    
    if ($sql == null) {
      $sql = "and i.closed = 0 ";
    }
    
    return $sql;
  }
  
  public function getOrderSql($ds) {
    $sql = "";
    $expressions = explode(",", $this->order);
    for ($i = 0; $i < count($expressions); $i++) {
      $operands = explode(" ", trim($expressions[$i]));
      
      if (count($operands) > 0) {
        if (array_key_exists($operands[0], $this->orders)) {
          if ($i != 0) {
            $sql .= ", ";
          }
          
          $sql .= $this->orders[$operands[0]];
          
          if (count($operands) > 1) {
            $sql .= strtolower($operands[1]) == "asc" ? " asc" : " desc";
          }
        }
      }
    }
    
    if ($sql == null) {
      $sql = "activity desc";
    }
    
    return $sql;
  }

  public static function fromPost($post) {
    $query = new Query;
    $query->name = $post["name"];
    $query->filter = $post["filter"];
    $query->order = $post["order"];
    return $query;
  }
}

?>
