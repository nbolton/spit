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

<form method="post" id="editor">
  <div class="box">
    
    <div class="row">
      <label for="title"><?=T_("Title")?>*</label>
      <input id="title" name="title" type="text" value="<?=htmlentities($issue->title, ENT_COMPAT, "UTF-8")?>" />
    </div>
    
    <div class="row">
      <div class="box suggestions" style="display: none">
        <p><?=T_("Check these suggestions first to make sure you don't create a duplicate.")?></p>
        <ul></ul>
      </div>
    </div>
    
    <?php if ($self->userCanEditAdvanced()): ?>
    <div class="column">
      <div class="row">
        <label for="trackerId"><?=T_("Tracker")?></label>
        <select id="trackerId" name="trackerId">
          <?php foreach($trackerSelect->options as $o): ?>
          <option value="<?=$o->value?>"<?=$o->getSelectedAttr()?>><?=$o->text?></option>
          <?php endforeach ?>
        </select>
      </div>
    </div>
    <?php endif ?>
    
    <div style="clear: both"></div>
    
    <div id="dynamicFields">
      <div class="loading">
        <img src="<?=$app->getImage("loading.gif")?>" />
      </div>
      <div class="column" id="column1"></div>
      <div class="column" id="column2"></div>
    </div>
    
    <div class="row">
      <label for="wmd-input"><?=T_("Details")?>*</label>
      <div id="wmd-button-bar" class="wmd-button-bar"></div>
      <textarea id="wmd-input" name="details" type="details" class="wmd-input"><?=$issue->details?></textarea>
    </div>
    
    <div class="row">
      <label for="wmd-input"><?=T_("Preview")?></label>
      <div id="wmd-preview" style="border: 1px solid #aaa; padding: .2em .5em"></div>
    </div>
    
  </div>
  
  
  <div class="preview">
  </div>
  
  <div class="buttons">
    <input type="submit" value="<?=($mode == \Spit\EditorMode::Create) ? T_("Create") : T_("Update")?>" >
  </div>
  
</form>
