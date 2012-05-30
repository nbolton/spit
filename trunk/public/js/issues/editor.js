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
      if (field.type == "select") {
        addSelectRow(field, index, data.length);
      }
    });
    columns.fadeIn();
    loading.hide();
  });
}

function addSelectRow(field, index, length) {
  
  template = $("div#templates div#rowWithSelect");
  row = template.clone();
  row.removeAttr("id");
  
  label = row.find("label");
  label.text(field.label);
  label.attr("for", field.name);
  
  select = row.find("select");
  select.attr("id", field.name);
  select.attr("name", field.name);

  for (key in field.options) {
    option = field.options[key];
    selected = option.selected ? " selected" : "";
    value = option.value != null ? option.value : "";
    select.append("<option value=\"{0}\"{1}>{2}</option>"
      .format(value, selected, option.text));
  }
  
  column = $("form div#column" + ((index < Math.ceil(length / 2)) ? 1 : 2));
  column.append(row);
}
