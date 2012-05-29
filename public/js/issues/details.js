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
  link = $("div.comment a.edit");
  
  link.click(function() {
    $(this).hide();
    form.fadeIn();
    scrollDown();
  });
  
  form.find("input.button").click(function() {
    content = form.find("textarea").val();
    log("sending comment: " + content);
    
    form.hide();
    loading = $("div.comment div.loading");
    loading.fadeIn();
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
      
      form.find("textarea").val("");
      loading.hide();
      link.show();
      scrollDown();
    },
    "json")
    .error(log("error"));
  });
}
