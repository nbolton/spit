function loadVariableFields() {
  
  $.getJSON("?getFieldsFor=1",
  {
    tags: "",
    tagmode: "any",
    format: "json"
  },
  function(data) {
    $.each(data, function(index, field) {
      if (field.type == "select") {
        addSelectRow(field, index, data.length);
      }
    });
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
    select.append("<option value=\"{0}\"{1}>{2}</option>".format(option.value, selected, option.text));
  }
  
  column = $("form div#column" + ((index >= Math.floor(length / 2)) ? 1 : 2));
  column.append(row);
}

function viewLoad() {
  loadVariableFields();
}
