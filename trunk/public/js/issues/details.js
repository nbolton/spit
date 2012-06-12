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
  initRelationsForm();
  initCommentsForm();
}

function initRelationsForm() {
  var form = $("span.relations div.form");
  
  var issues = $("span.relations ul.issues");
  if (issues.find("li").length == 0) {
    issues.hide();
  }
  
  var idField = form.find("input[name='issueId']");
  
  $("span.relations a.add").click(function() {
    $(this).hide();
    $("span.relations div.form").fadeIn();
    idField.focus();
  });
  
  var add = $("span.relations input[name='add']");
  
  idField.keyup(function(event){
    // click add when user presses enter.
    if (event.keyCode == 13) {
      add.click();
    }
  });
  
  $("span.relations a.cancel").click(function() {
    hideRelationForm();
  });
  
  var deleteFunc = function() {
    var idField = $(this).parent().find("input[type='hidden']");
    var id = idField.val();
    
    log("deleting relation: id=" + id);
    idField.parent().remove();
    
    $.getJSON("?deleteRelation", {
      format: "json",
      id: id
    },
    function(message) {
      updateLoadStats(message["stats"]);
      data = message["data"];
    });
  }
  
  add.click(function() {
    
    var issueId = idField.val();
    var type = form.find("select[name='type'] option:selected").val();
    log("adding relation: type=" + type + ", issueId=" + issueId);
    
    form.hide();
    $("span.relations div.loading").fadeIn();
    
    $.ajax({
      dataType: "json",
      type: "post",
      url: "?createRelation",
      data: {
        format: "json",
        issueId: issueId,
        type: type
      },
      success: function(message) {
        updateLoadStats(message["stats"]);
        var data = message["data"];
        
        hideRelationForm();
        
        if (data.error) {
          form.show();
          alert(data.error);
        }
        else {
          // only reset id textbox, leave type as is in case user wants to 
          // add another of the same type.
          form.find("input[name='issueId']").val("");
          
          var info = $("<li>" + data.info + "</li>");
          info.find("a.delete").click(deleteFunc);
          $("span.relations ul.issues").show().append(info);
          
          if (data.newStatus) {
            log("new status: " + data.newStatus);
            $("div#statusId div.value").html(data.newStatus);
          }
        }
      },
      error: function(xhr, textStatus, error) {
        log(xhr.statusText);
        log(textStatus);
        log(error);
      }
    });
  });
  
  $("span.relations a.delete").click(deleteFunc);
}

function hideRelationForm() {
  $("span.relations div.loading").hide();
  $("span.relations div.form").hide();
  $("span.relations a.add").show();
}

function initCommentsForm() {
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
    
    $.post("?comment", {
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
