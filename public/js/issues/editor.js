function loadVariableFields() {
  
  $.getJSON("?getFieldsFor=1",
  {
    tags: "",
    tagmode: "any",
    format: "json"
  },
  function(data) {
    $.each(data, function(i, field) {
      console.log("field: name=" + field.name + ", label=" + field.label + ", type=" + field.type);
      if (field.type == "select") {
        for (key in field.options) {
          option = field.options[key];
          console.log("option: name=" + option.name + ", value=" + option.value);
        }
      }
    });
  });
}

function viewLoad() {
  loadVariableFields();
}
