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
    <img src="<?=$app->getImagePath("edit.png")?>">Edit</img>
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
    <hr />
    <p>
      <i><?=$change->getDateString()?></i>: <?=$change->creator?>
      <?=$change->getTypeString()?> <?=$change->name?>.
    </p>
    <?php if ($change->content != ""): ?>
    <p><?=$change->getContentHtml()?></p>
    <?php endif ?>
    <?php endforeach ?>
  </div>
</div>
