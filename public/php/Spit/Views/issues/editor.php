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

<?php if($saved): ?>
<p><?=T_("Issue saved.")?></p>
<?php else: ?>
<form method="post">
  <div class="box">
    <div class="column">
      <div class="row">
        <label for="tracker"><?=T_("Tracker")?></label>
        <select id="tracker" name="tracker">
          <option value="1" selected="selected">Bug</option>
          <option value="2">Feature</option>
          <option value="3">Support</option>
          <option value="4">Task</option>
        </select>
      </div>
    </div>
    <div class="row">
      <label for="title"><?=T_("Title")?></label>
      <input id="title" name="title" type="text" class="text" />
    </div>
    <div class="row">
      <label for="details"><?=T_("Details")?></label>
      <textarea id="details" name="details" type="details"></textarea>
    </div>
    <div id="dynamicFields">
      <div class="loading">
        <img src="<?=$app->getThemeRoot()?>/image/loading.gif" />
      </div>
      <div class="column" id="column1"></div>
      <div class="column" id="column2"></div>
    </div>
  </div>
  <div class="buttons">
    <input type="submit" value="Create" >
  </div>
</form>
<div id="templates">
  <div class="row" id="rowWithSelect">
    <label></label>
    <select></select>
  </div>
</div>
<?php endif ?>
