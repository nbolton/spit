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
<p><a href="new/"><?=T_("New issue")?></a></p>
<table>
  <thead>
  <tr>
    <th class="checkbox"><a href="javascript:void(0)"><img src="<?=$app->getImage("toggle_check.png")?>"></a></th>
    <?php foreach($fields as $field): ?>
    <th class="<?=$field->name?>"><a href="javascript:void(0)"><?=$field->label?></a></th>
    <?php endforeach ?>
  </tr>
  </thead>
  <tbody>
    <?php foreach($issues as $issue): ?>
    <tr id="issue-<?=$issue->id?>">
      <td class="checkbox"><input name="id" type="checkbox" value="<?=$issue->id?>"></td>
      <?php foreach($fields as $field): ?>
      <td class="<?=$field->name?><?=$field->compact ? " compact" : ""?>">
        <?=$self->getValue($issue, $field->name)?>
      </td>
      <?php endforeach ?>
    </tr>
    <?php endforeach ?>
  </tbody>
</table>
