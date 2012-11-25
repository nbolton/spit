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

var searching = false;
var searchText = "";

function viewLoad() {
  $("form#editor").submit(function() {
    title = $("input[name='title']");
    details = $("textarea[name='details']");
    
    invalid = 0;
    if (title.val() == "") {
      title.parent().find("label").css("color", "red");
      invalid++;
    }
    if (details.val() == "") {
      details.parent().find("label").css("color", "red");
      invalid++;
    }
    
    return invalid == 0;
  });
  
  tracker = $("select#trackerId");
  if (tracker.is("*")) {
    loadDynamicFields(tracker.val());
  
    tracker.change(function() {
      loadDynamicFields($(this).val());
    });
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
  
  title = $("input[name='title']");
  title.keyup(function() {
    updateSearch($(this).val());
  });
  
  updateSearch(title.val());
}

function loadDynamicFields(tracker) {
  log("loading fields for: " + tracker);
  
  columns = $("div#dynamicFields div.column");
  columns.hide();
  
  loading = $("form div.loading");
  loading.show();
  
  $.getJSON("", {
    format: "json",
    tracker: tracker
  },
  function(message) {
    
    updateLoadStats(message["stats"]);
    data = message["data"];
    
    columns.empty();
    $.each(data, function(index, field) {
      log(field.type);
      if (field.type == "select") {
        addSelectRow(field, index, data.length);
      }
      else if (field.type == "text") {
        addTextRow(field, index, data.length);
      }
    });
    columns.fadeIn();
    loading.hide();
  });
}

function addTextRow(field, index, length) {
  row = $("<div></div>");
  row.attr("class", "row");
  
  label = $("<label></label>");
  row.append(label);
  label.text(field.label);
  label.attr("for", field.name);
  
  text = $("<input type=\"text\" />");
  row.append(text);
  text.attr("id", field.name);
  text.attr("name", field.name);
  text.val(field.value);
  
  addRow(row, index, length);
}

function addSelectRow(field, index, length) {
  row = $("<div></div>");
  row.attr("class", "row");
  
  label = $("<label></label>");
  row.append(label);
  label.text(field.label);
  label.attr("for", field.name);
  
  select = $("<select></select>");
  row.append(select);
  select.attr("id", field.name);
  select.attr("name", field.name);

  for (key in field.options) {
    option = field.options[key];
    selected = option.selected ? " selected" : "";
    value = option.value != null ? option.value : "";
    select.append("<option value=\"{0}\"{1}>{2}</option>"
      .format(value, selected, option.text));
  }
  
  addRow(row, index, length);
}

function addRow(row, index, length) {
  column = $("form div#column" + ((index < Math.ceil(length / 2)) ? 1 : 2));
  column.append(row);
}

function updateSearch(text) {
  log("search: text: " + text);
  var suggestions = $("form#editor div.suggestions");
  
  if (text == "") {
    suggestions.hide();
    return;
  }
  
  delay(function() {
    
    $.getJSON("", {
      format: "json",
      search: text
    },
    function(message) {
      log("search: response: " + text);
      suggestions.find("ul").empty();
      
      if (message.data.length) {
        suggestions.show();
      }
      else {
        suggestions.hide();
        return;
      }
      
      for (i in message.data) {
        result = message.data[i];
        log(result);
        var li = $("<li><a href=\"{0}\">{1} #{2}</a> - {3}</li>"
          .format(result.url, result.tracker, result.id, result.title));
        suggestions.find("ul").append(li);
      }
    });
  }, 500);
}
