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
?>

<h2><?=$title?></h2>

<form method="post">
  <div class="box">
    <div class="column">
      <div class="row">
        <label for="trackerId"><?=T_("Tracker")?></label>
        <select id="trackerId" name="trackerId">
          <option value="1" selected="selected">Bug</option>
          <option value="2">Feature</option>
          <option value="3">Support</option>
          <option value="4">Task</option>
        </select>
      </div>
    </div>
    <div class="row">
      <label for="title"><?=T_("Title")?></label>
      <input id="title" name="title" type="text" class="text" value="<?=$issue->title?>" />
    </div>
    <div class="row">
      <label for="details"><?=T_("Details")?></label>
      <textarea id="details" name="details" type="details"><?=$issue->details?></textarea>
    </div>
    <div id="dynamicFields">
      <div class="loading">
        <img src="<?=$app->getImagePath("loading.gif")?>" />
      </div>
      <div class="column" id="column1"></div>
      <div class="column" id="column2"></div>
    </div>
  </div>
  <div class="buttons">
    <input type="submit" value="<?=($mode == \Spit\EditorMode::Create) ? T_("Create") : T_("Update")?>" >
  </div>
</form>
<div id="templates">
  <div class="row" id="rowWithSelect">
    <label></label>
    <select></select>
  </div>
</div>
