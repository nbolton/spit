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
?>

<html>
  <head>
    <title><?=$settings->site->title?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta name="description" content="<?=$settings->site->description?>" />
    <link rel="stylesheet" type="text/css" href="<?=$root?>/theme/default/main.css" />
  </head>
  <body>
    <div class="layout">
      <div class="header">
        <div class="headerContent">
          <h1><?=$settings->site->title?></h1>
          <p><?=$settings->site->description?></p>
          <div class="links">
            <a href="<?=$root?>/">Home</a>,
            <a href="<?=$root?>/issues/">Issues</a>
          </div>
        </div>
      </div>
      <div class="content">
        <?php require $content; ?>
      </div>
      <div class="footer">
        <div class="footerContent">
          <p>Powered by <a href="http://spit-foss.org">SPIT</a>: Simple PHP Issue Tracker. Copyright &copy; Nick Bolton 2012.</p>
        </div>
      </div>
    </div>
  </body>
</html>
