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
    <div id="variables">
      <div class="column" id="column1"></div>
      <div class="column" id="column2"></div>
      <div class="row" style="display: none">
        <label></label>
        <select></select>
      </div>
    </div>
    <div class="column">
      <div class="row">
        <label for="status"><?=T_("Status")?></label>
        <select id="status" name="status">
          <option value="1" selected="selected">New</option>
          <option value="12">Reviewed</option>
          <option value="13">Accepted</option>
          <option value="8">PatchesWelcome</option>
          <option value="10">GotPatch</option>
          <option value="2">InProgress</option>
          <option value="5">Fixed</option>
          <option value="16">WontFix</option>
          <option value="17">Invalid</option>
          <option value="27">Duplicate</option>
          <option value="29">CannotReproduce</option>
        </select>
      </div>
      <div class="row">
        <label for="priority"><?=T_("Priority")?></label>
        <select id="priority" name="priority">
          <option value="3">Low</option>
          <option value="4" selected="selected">Normal</option>
          <option value="5">High</option>
          <option value="6">Urgent</option>
          <option value="7">Immediate</option>
        </select>
      </div>
      <div class="row">
        <label for="version"><?=T_("Version")?></label>
        <select id="version" name="version">
          <option value=""></option>
          <option value="39">1.4.9</option>
        </select>
      </div>
    </div>
    <div class="column">
      <div class="row">
        <label for="platform"><?=T_("Platform")?></label>
        <select id="platform" name="platform">
          <option value=""></option>
          <option value="Windows">Windows</option>
          <option value="Mac OS X">Mac OS X</option>
          <option value="Linux">Linux</option>
          <option value="Unix">Unix</option>
          <option value="Various">Various</option>
        </select>
      </div>
      <div class="row">
        <label for="assignee"><?=T_("Assignee")?></label>
        <select id="assignee" name="assignee">
          <option value=""></option>
          <option value="40">Brendon Justin</option>
          <option value="4">Chris Schoeneman</option>
          <option value="49">Ed Carrel</option>
          <option value="10">Jason Axelson</option>
          <option value="482">Jean-Sébastien Dominique</option>
          <option value="2158">Jodi Jones</option>
          <option value="3">Nick Bolton</option>
          <option value="5">Sorin Sbârnea</option>
          <option value="57">Syed Amer Gilani</option>
        </select>
      </div>
    </div>
  </div>
  <div class="buttons">
    <input type="submit" value="Create" >
  </div>
</form>
<?php endif ?>
