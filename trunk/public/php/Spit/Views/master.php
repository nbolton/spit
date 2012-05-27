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
    <title><?=$fullTitle?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta name="description" content="<?=$app->getSiteDescription()?>" />
    <link rel="stylesheet" type="text/css" href="<?=$app->getThemeRoot()?>/style/main.css" />
    <?=$app->controller->getViewStyle($view);?>
    <script type="text/javascript" src="<?=$app->getProjectRoot()?>js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="<?=$app->getProjectRoot()?>js/common.js"></script>
    <?=$app->controller->getViewScript($view);?>
  </head>
  <body>
    <div class="layout">
      <div class="header">
        <div class="headerContent">
          <h1><?=$app->getSiteTitle()?></h1>
          <p><?=$app->getSiteDescription()?></p>
          <div class="links">
          <?php if(isset($app->project)): ?>
          <?php foreach ($app->links as $k => $l): ?>
            <a href="<?=$app->getFullLink($l)?>"><?=$l->name?></a><?=($k != end(array_keys($app->links)) ? "," : "")?>
          <?php endforeach ?>
          <?php endif ?>
          </div>
          <div class="language">
            <button id="language">
              <span class="text"><?=T_("Language:")?> <?=$app->locale->getCurrent()->name?></span>
              <img src="<?=$app->getImage("pixel.gif")?>" class="arrow" />
            </button>
            <div class="menu">
              <?php foreach ($app->locale->getLanguages() as $l): ?>
              <div class="item"><a href="?lang=<?=$l->code?>"><?=$l->name?></a></div>
              <?php endforeach ?>
            </div>
          </div>
        </div>
      </div>
      <div class="content">
        <?php require $content; ?>
      </div>
      <div class="footer">
        <div class="footerContent">
          <p>
          <?php
            echo sprintf(
              T_("Powered by %s. Copyright &copy; %s 2012."),
              "<a href=\"http://spit-foss.org\">SPIT</a>: Simple PHP Issue Tracker", "Nick Bolton");
          ?>
          </p>
        </div>
      </div>
    </div>
  </body>
</html>
