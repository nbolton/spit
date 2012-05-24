<?php

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

// useful itef codes list:
//   http://www.lingoes.net/en/translator/langcode.htm

namespace Spit;

class Language {
  function __construct($code, $name) {
    $this->code = $code;
    $this->name = $name;
  }
}

class Locale {
  
  var $lang;
  var $rtl;
  
  function __construct() {
    
    // default language is english
    $this->lang = "en";
    
    // right to left text languages
    $this->rtl = array("ar", "he");
    
    $this->languages = array(
      new Language("en", "English"),
      new Language("de", "Deutsch")
    );
  }

  function fixItefTag($tag) {
    
    $split = explode("-", $tag);
    if (count($split) == 2) {
      // if language code and country code are the same, then we have
      // a redudntant itef tag (e.g. de-de or fr-fr).
      if (strtolower($split[0]) == strtolower($split[1])) {
        return strtolower($split[0]);
      }
    }
    
    // make sure the tag is always lower so that it looks better in the url.
    return strtolower($tag);
  }

  function parseHeaderLocale($header) {
    $first = reset(explode(";", $header));
    $first = reset(explode(",", $first));
    $itef = str_replace("_", "-", $first);
    $lower = strtolower($itef);
    if ($lower != "") {
      return $lower;
    }
    return "en";
  }

  function toGnu($lang) {
    // norway does not confirm to GNU! :|
    if ($lang == "nn" || $lang == "nb")
      return "no";

    // if the language is region specific, use an underscore,
    // and make sure the country code is capitalized.
    if (strstr($lang, "-")) {  
      $split = preg_split("/-/", $lang);
      return $split[0] . "_" . strtoupper($split[1]);
    }
    return $lang;
  }
  
  function run() {
    
    if (isSet($_GET["lang"]) && ($_GET["lang"] != "")) {
      
      // language forced by url.
      $this->lang = $_GET["lang"];
      $_SESSION["lang"] = $this->lang;
      
    } else if (isSet($_SESSION["lang"])) {

      // language forced. this should only happen under /
      // where no url lang is forced.
      $this->lang = $_SESSION["lang"];
      
    } else if (isSet($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
      
      // no language specified in url, try to auto-detect.
      $this->lang = $this->parseHeaderLocale($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    }
    
    $gnuLang = $this->toGnu($this->lang);
    putenv("LANGUAGE=" . $gnuLang);
    putenv("LANG=" . $gnuLang);
    putenv("LC_ALL=" . $gnuLang);
    putenv("LC_MESSAGES=" . $gnuLang);
    T_setlocale(LC_ALL, $gnuLang);
    T_bindtextdomain("spit", "./locale");
    T_textdomain("spit");
  }
  
  function getLangDir() {
    if (in_array($this->lang, $this->rtl)) {
      return "rtl";
    }
    return "ltr";
  }
  
  function getCountry() {
    return reset(explode("-", $this->lang));
  }
  
  function getLanguages() {
    return $this->languages;
  }
  
  function getCurrent() {
    foreach ($this->getLanguages() as $l) {
      if ($l->code == $this->lang) {
        return $l;
      }
    }
  }
}

?>
