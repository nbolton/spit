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
  <?php if($self->userCanEdit()): ?>
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
  
  <hr />
  <p><?=Markdown($issue->details)?></p>
  
  <div class="changes">
    <?php foreach($changes as $change): ?>
    <div class="change">
      <hr />
      <span class="info"><?=$self->getChangeInfo($change)?></span>
      <?php if ($change->content != ""): ?>
      <span class="content"><?=$self->getChangeContent($change)?></span>
      <?php endif ?>
    </div>
    <?php endforeach ?>
  </div>
</div>

<div class="comment">
  <p><a class="edit" href="javascript:void(0)">
    <img src="<?=$app->getImagePath("edit.png")?>"/>Write comment</a>
  </p>
  <form>
    <textarea name="content"></textarea>
    <input class="button" type="button" value="<?=T_("OK")?>" />
  </form>
</div>

<div id="templates">
  <div class="change">
    <hr />
    <span class="info"></span>
    <span class="content"></span>
  </div>
</div>
