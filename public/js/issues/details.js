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
  
  $("div.comment a.edit").click(function() {
    form.fadeIn();
  });
  
  form.find("input.button").click(function() {
    content = form.find("textarea").val();
    log("sending comment: " + content);
    
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
      
      form.fadeOut();
    },
    "json")
    .error(log("error"));
  });
}
