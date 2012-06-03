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
  tracker = $("select#trackerId");
  loadDynamicFields(tracker.val());
  
  tracker.change(function() {
    loadDynamicFields($(this).val());
  });
  
  var converter = Markdown.getSanitizingConverter();
  var editor = new Markdown.Editor(converter);
  editor.run();
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
