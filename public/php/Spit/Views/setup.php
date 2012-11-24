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

<?php if (!$app->security->isLoggedIn()): ?>

<p><?=T_("Please login to continue (top right of page).");?></p>

<?php else: ?>

<form method="post">
  <div class="box">
    
    <div class="title" style="padding-top: 0px">
      <h3><?=T_("Projects")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label><?=T_("List")?></label>
<textarea name="projects">
spit: SPIT
myproj: My Project
</textarea>
        <p>List the projects you want to create using the format <code>name: title</code>, one on each line.</p>
      </div>
    </div>
    
    <div class="title">
      <h3><?=T_("Trackers")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label><?=T_("CSV")?></label>
        <input type="textbox" name="trackers" value="Bug, Feature, Task" class="long" />
        <p></p>
      </div>
    </div>
    
    <div class="title">
      <h3><?=T_("Status")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label><?=T_("CSV")?></label>
        <input type="textbox" name="statuses" value="New, Accepted, In Progress, Fixed, Invalid, Duplicate" class="long" />
        <p></p>
      </div>
    </div>
    
    <div class="title">
      <h3><?=T_("Priorities")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label><?=T_("CSV")?></label>
        <input type="textbox" name="priorities" value="Low, Normal, High, Urgent, Immediate" class="long" />
        <p></p>
      </div>
    </div>
    
    <div class="title">
      <h3><?=T_("Categories")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label><?=T_("CSV")?></label>
        <input type="textbox" name="categories" value="GUI, System, Build" class="long" />
        <p></p>
      </div>
    </div>
    
  </div>
  
  <div class="buttons">
    <input type="submit" value="<?=T_("Continue")?>" >
  </div>
</form>

<?php endif ?>
