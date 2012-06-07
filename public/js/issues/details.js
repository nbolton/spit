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

function viewLoad() {
  form = $("div.comment form");
  
  // if user isn't logged in, then there is no form.
  if (form.length == 0) {
    return;
  }
  
  var converter = Markdown.getSanitizingConverter();
  var editor = new Markdown.Editor(converter);
  editor.run();
  
  editor.hooks.chain("onPreviewRefresh", function() {
    if ($("textarea#wmd-input").val() == "") {
      $("div.preview").hide();
    }
    else {
      $("div.preview").show();
    }
  });
  
  $("a#writeComment").click(function() {
    showCommentsBox();
  });
  
  $("a#cancelComment").click(function() {
    hideCommentsBox();
  });
  
  form.find("input.button").click(function() {
    content = form.find("textarea").val();
    log("sending comment: " + content);
    
    form.hide();
    $("div.comment div.loading").fadeIn();
    scrollDown();
    
    $.post("", {
      format: "json",
      content: content
    },
    function(message) {
      updateLoadStats(message["stats"]);
      data = message["data"];
      log(data);
      
      change = $("div#templates div.change").clone();
      change.find("span.info").html(data.info);
      change.find("span.content").html(data.html);
      $("div.changes").append(change);
      
      hideCommentsBox();
    },
    "json")
    .error(log("error"));
  });
}

function showCommentsBox() {
  $("a#writeComment").hide();
  $("div.preview").hide();
  $("div.comment form").fadeIn();
  $("div.comment form textarea").focus();
  scrollDown();
}

function hideCommentsBox() {
  $("div.comment form textarea").val("");
  $("div.comment form").hide();
  $("div.comment div.loading").hide();
  $("a#writeComment").show();
  $("div.preview").hide();
  scrollDown();
}
