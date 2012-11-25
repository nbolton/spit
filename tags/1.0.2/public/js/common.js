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

$(function() {
  $("button#language").click(function() {
    pos = $(this).position();
    width = $(this).outerWidth();
    height = $(this).outerHeight();
    menu = $("div.language div.menu");
    menu.css({
      position: "absolute",
      top: (pos.top + height - 5) + "px",
      left: pos.left + "px",
      width: (width - 23) + "px",
    });
    menu.slideDown(100);
  });
  
  if ("viewLoad" in self) {
    viewLoad();
  }
  
  initialLoadTime = parseFloat($("span.loadTime").text());
  initialQueries = parseInt($("span.queries").text());
  
  log("initial load time: {0} ms".format(initialLoadTime));
  log("initial db queries: " + initialQueries);
});

String.prototype.format = function() {
  var args = arguments;
  return this.replace(/{(\d+)}/g, function(match, number) { 
    return typeof args[number] != 'undefined' ? args[number] : match;
  });
};

function log(s) {
  if ("console" in self && "log" in console) console.log(s);
}

function getParam(name) {
  var match = RegExp("#?" + name + "=([^&]*)").exec(window.location.hash);
  return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
}

function updateLoadStats(stats) {
  var loadTime = parseFloat(stats["loadTime"]);
  var queries = parseFloat(stats["queries"]);
  
  log("ajax load time: {0} ms".format(loadTime.toFixed(2)));
  log("ajax db queries: " + queries);
  
  $("span.loadTime").text((initialLoadTime + loadTime).toFixed(2));
  $("span.queries").text(initialQueries + queries);
}

function scrollUp() {
  $('html, body').animate({scrollTop: 0}, 500);
}

function scrollDown() {
  $('html, body').animate({scrollTop: $(document).height()}, 500);
}

var delay = (function(){
  var timer = 0;
  return function(callback, ms) {
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();
