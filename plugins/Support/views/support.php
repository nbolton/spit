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

<style type="text/css">
div.field,
div.error {
  padding: 5px;
  margin: 5px;
}

div.error {
  color: #ff5544;
  display: none;
}

div.error p {
  padding: 0px;
  margin: 0px;
}

textarea.support {
  height: 120px;
}
</style>

<h2><?=T_("Report bug")?></h2>

<form method="post">
  <div class="box" style="padding:0px">
    <div class="error">
      <p><?=T_("One or more fields were empty.")?></p>
    </div>
    
    <div class="field">
      <h3><?=T_("Summary")?></h3>
      <input name="title" type="text" />
    </div>
    
    <div class="field">
      <h3><?=T_("Steps to reproduce bug")?></h3>
      <p><?=T_("Complete the numbered steps needed to reproduce the problem.")?></p>
<textarea name="steps" class="support">
1. Disengage warp drive
2. Reverse the sheild polarity
3. Engage the Heisenberg compensators

Expected: McChicken sandwich rematerialized

Actual: LCARS console exploded</textarea>
    </div>
    
    <div class="field">
      <h3><?=T_("Versions and operating systems")?></h3>
      <p><?=T_("What version of software and operating systems are you using? Example: v1.0 on Windows 7")?></p>
      <textarea name="versions" class="support"></textarea>
    </div>
    
    <div class="field">
      <h3><?=T_("Temporary workarounds")?></h3>
      <p><?=T_("Were you able to temporarily fix your problem? e.g.: by restarting your computer.")?></p>
      <textarea name="workaround" class="support"></textarea>
    </div>
    
    <div class="field">
      <h3><?=T_("Additional comments")?></h3>
      <p><?=T_("If you have any more information to provide, please enter it here.")?></p>
      <textarea name="comments" class="support"></textarea>
    </div>
  </div>
  <div class="buttons">
    <input type="button" value="Send" id="send" >
  </div>
</form>

<script type="text/javascript">
function viewLoad() {
  $("input#send").click(function() {
    var invalid = 0;
    if (!validate($("input[name='title']"))) {
      invalid++;
    }
    if (!validate($("textarea[name='steps']"))) {
      invalid++;
    }
    if (!validate($("textarea[name='versions']"))) {
      invalid++;
    }
    log(invalid);
    if (invalid == 0) {
      $("form").submit();
    }
    else {
      scrollUp();
      $("div.error").show();
    }
  });
}

function validate(element) {
  if (element.val() == "") {
    element.parent().css("box-shadow", "0px 0px 20px #ff5544");
    element.parent().css("border", "1px solid #ff5544");
    return false;
  }
  else {
    element.parent().css("box-shadow", "");
    element.parent().css("border", "");
    return true;
  }
}
</script>
