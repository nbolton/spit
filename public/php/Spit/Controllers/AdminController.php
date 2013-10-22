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

require_once "php/Spit/Importer.php";

class AdminController extends Controller {
  
  public function __construct() {
    $this->siteWide = true;
  }

  public function run() {
    if (!$this->auth(\Spit\UserType::Admin)) {
      return;
    }
    
    switch ($this->getPathPart(1)) {
      case "": $this->showView("admin/index", T_("Admin")); break;
      case "import": $this->runImport(); break;
      default: $this->showError(404); break;
    }
  }
  
  public function runImport() {
    if ($this->isPost() && ($_POST["app"] == "redmine")) {
      $db = new \stdClass();
      $this->applyFormValues($db, "db");
      
      $form = new \stdClass();
      $this->applyFormValues($form, null);
      
      $options = new \stdClass();
      $options->clear = isset($form->clear) && ($form->clear == "on");
      $options->db = $db;
      
      // map redmine ids to spit field names.
      $options->customMap = array();
      $options->customMap[3] = "googleId";
      $options->customMap[6] = "platformId";
      
      // redmine custom id for the foundId field.
      $options->foundIdCustom = 4;
      
      // get the following fields by copying the other.
      $options->copyFields["redmineId"] = "importId";
      
      $importer = new \Spit\Importer($this->app);
      $importer->redmineImport($options);
    }
    
    $this->showView("admin/import", T_("Import"));
  }
}

?>
