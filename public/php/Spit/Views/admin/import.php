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
    <div class="title" style="padding-top: 0px">
      <h3><?=T_("Source")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label><?=T_("Application")?></label>
        <select name="app">
          <option value="redmine">Redmine</option>
        </select>
      </div>
    </div>
    
    <div class="title">
      <h3><?=T_("Database")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label><?=T_("Host")?></label>
        <input type="textbox" name="db[host]" value="localhost" />
      </div>
      <div class="row">
        <label><?=T_("Name")?></label>
        <input type="textbox" name="db[name]" value="redmine" />
      </div>
      <div class="row">
        <label><?=T_("User")?></label>
        <input type="textbox" name="db[user]"value="root"  />
      </div>
      <div class="row">
        <label><?=T_("Password")?></label>
        <input type="textbox" name="db[password]" />
      </div>
    </div>
    
    <div class="title">
      <h3><?=T_("Options")?></h3>
      <hr />
    </div>
    <div class="column">
      <div class="row">
        <label class="checkbox">
          <input name="clear" type="checkbox" class="checkbox" checked="yes" /> <?=T_("Clear SPIT database?")?>
        </label>
      </div>
    </div>
  </div>
  
  <div class="buttons">
    <input type="submit" value="<?=T_("Import")?>" >
  </div>
</form>
