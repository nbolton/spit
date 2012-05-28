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
  page = getParam("page");
  if (page == null) {
    page = 1;
  }
  
  results = getParam("results");
  if (results == null) {
    results = 15;
  }
  
  showLoading(false, 100);
  loadIssues(page, results);
  
  $("div.paging a.next").click(function() {
    if (page < pageCount) {
      showLoading(true, 350);
      loadIssues(++page, results);
    }
  });
  
  $("div.paging a.back").click(function() {
    if (page > 1) {
      showLoading(true, 350);
      loadIssues(--page, results);
    }
  });
}

function showLoading(absolute, timeout) {
  setTimeout(function() {
    if (loadComplete) {
      return;
    }
    
    loading = $("div.loading");
    
    if (absolute) {
      loading.css("position", "absolute");
      table = $("div#issues table");
      loading.height(table.height());
      loading.width(table.width());
    }
    
    loading.show();
  }, timeout);
}

function loadIssues(page, results) {
  
  log("loading: page={0}, results={1}".format(page, results));
  
  table = $("div#issues table");
  
  // put page in url so users can copy the link.
  window.location.replace("#page={0}&results={1}".format(page, results));
  
  loadComplete = false;
  
  $.getJSON("", {
    format: "json",
    page: page,
    results: results
  },
  function(message) {
    
    updateLoadStats(message["stats"]);
    data = message["data"];
    
    pageCount = data.pageCount;
    $("div.paging span.page").text(page);
    $("div.paging span.pageCount").text(pageCount);
    
    table = $("div#templates table.issues").clone();
    table.hide();
    $("div#issues table").replaceWith(table);
    table.removeAttr("class");
    table.attr("id", "issues");
    
    header = table.find("thead tr");
    
    $.each(data.fields, function(index, field) {
      th = $("<th></th>");
      header.append(th);
      
      a = $("<a></a>");
      th.append(a);
      
      th.attr("style", field.name);
      a.text(field.label);
      a.attr("href", "javascript:void(0)");
    });
    
    tbody = table.find("tbody");
    tbody.find("tr").remove();
    
    $.each(data.issues, function(index, issue) {
      tr = $("div#templates table.issues tbody tr").clone();
      tbody.append(tr);
      
      $.each(data.fields, function(index, field) {
        td = $("<td></td>");
        tr.append(td);
        
        value = issue[field.name] != null ? issue[field.name] : "";
        if (field.link) {
          a = $("<a></a>");
          td.append(a);
          
          a.text(value);
          a.attr("href", "details/{0}/".format(issue.id));
        }
        else {
          td.text(value);
        }
        
        compact = field.compact ? " compact" : "";
        td.attr("class", field.name + compact);
      });
    });
    
    loadComplete = true;
    $("div.loading").hide();
    $("div.paging").fadeIn();
    table.fadeIn();
  });
}
