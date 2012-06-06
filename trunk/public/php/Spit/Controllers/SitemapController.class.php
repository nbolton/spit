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

namespace Spit\Controllers;

class SitemapController extends Controller {
  
  public function run() {
    $dataStore = new \Spit\DataStores\IssueDataStore;
    $issues = $dataStore->getPublicIds();
    $links = "";
    
    foreach ($issues as $issue) {
      $links .= sprintf(
        "<url><loc>http://%s%s</loc></url>",
        $_SERVER["HTTP_HOST"],
        $this->app->linkProvider->forIssue($issue->id));
    }
    
    $format = "<?xml version=\"1.0\" ?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">%s</urlset>";
    exit(sprintf($format, $links));
  }
}

?>
