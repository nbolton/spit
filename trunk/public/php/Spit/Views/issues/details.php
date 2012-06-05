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

<h3><?=$title?></h3>
<div class="box">
  <?php if($self->userCanEdit($issue, true)): ?>
  <a href="<?=$app->getProjectRoot()?>issues/edit/<?=$issue->id?>/">
    <img src="<?=$app->getImagePath("edit.png")?>"/>Edit
  </a>
  
  <hr />
  <?php endif ?>
  <?php foreach($columns as $column): ?>
  <div class="column">
    <?php foreach($column as $field): ?>
      <div class="row">
        <div class="label"><?=$field->label?></div>
        <div class="value"><?=$field->value?></div>
      </div>
    <?php endforeach ?>
  </div>
  <?php endforeach ?>
  
  <?php if (trim($issue->details) != ""): ?>
  <hr />
  <span class="details">
    <?=Markdown($issue->details)?>
  </span>
  <?php endif ?>
  
  <?php if (count($relations) != 0): ?>
  <hr />
  <span class="relations">
    <ul>
    <?php foreach($relations as $relation): ?>
      <li><?=$self->getRelationInfo($relation, $issue->id)?></li>
    <?php endforeach ?>
    </ul>
  </span>
  <?php endif ?>
  
  <div class="changes">
    <?php foreach($changes as $change): ?>
    <div class="change">
      <hr />
      <span class="info"><?=$self->getChangeInfo($change)?></span>
      <span class="content"><?=$self->getChangeContent($change)?></span>
    </div>
    <?php endforeach ?>
  </div>
</div>

<div class="comment">
  <p><a class="edit" href="javascript:void(0)">
    <img src="<?=$app->getImagePath("edit.png")?>"/>Write comment</a>
  </p>
  <form>
    <div id="wmd-button-bar" class="wmd-button-bar"></div>
    <textarea id="wmd-input" name="content" class="wmd-input"></textarea>
    <input class="button" type="button" value="<?=T_("OK")?>" />
    <div class="preview">
      <div id="wmd-preview" class="box"></div>
    </div>
  </form>
  <div class="loading">
    <img src="<?=$app->getImagePath("loading.gif")?>" />
  </div>
</div>

<div id="templates">
  <div class="change">
    <hr />
    <span class="info"></span>
    <span class="content"></span>
  </div>
</div>
