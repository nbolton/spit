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

<html lang="<?=$app->locale->lang?>" xml:lang="<?=$app->locale->lang?>">
  <head>
    <title><?=$fullTitle?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Language" content="<?=$app->locale->lang?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta name="description" content="<?=$app->getSiteDescription()?>" />
    
    <link rel="stylesheet" type="text/css" href="<?=$app->getThemeRoot()?>/style/main.css" />
    <?=$app->controller->getViewStyle($view);?>
    <?php if ($self->useMarkdown): ?>
    <link rel="stylesheet" type="text/css" href="<?=$app->getThemeRoot()?>/style/pagedown.css" />
    <?php endif ?>
    
    <script type="text/javascript" src="<?=$app->getRoot()?>js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="<?=$app->getRoot()?>js/common.js"></script>
    <?php if ($self->useMarkdown): ?>
    <script type="text/javascript" src="<?=$app->getRoot()?>js/pagedown/Markdown.Converter.js"></script>
    <script type="text/javascript" src="<?=$app->getRoot()?>js/pagedown/Markdown.Sanitizer.js"></script>
    <script type="text/javascript" src="<?=$app->getRoot()?>js/pagedown/Markdown.Editor.js"></script>
    <style type="text/css">
    .wmd-button > span {
      background-image: url(<?=$app->getImagePath("wmd-buttons.png")?>);
    }
    </style>
    <?php endif ?>
    <?=$app->controller->getViewScript($view);?>
    
    <?php if(isset($app->settings->site->googleAnalytics)): ?>
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', '<?=$app->settings->site->googleAnalytics?>']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
    <?php endif ?>
  </head>
  <body>
    <div class="layout">
      <div class="header">
        <div class="headerContent">
          <div class="column">
            <h1><?=$app->getSiteTitle()?></h1>
            <p><?=$app->getSiteDescription()?></p>
            <div class="links">
            <?php foreach ($app->links as $k => $l): ?>
              <a href="<?=$app->getFullLink($l)?>"><?=$l->name?></a><?=($k != end(array_keys($app->links)) ? "," : "")?>
            <?php endforeach ?>
            </div>
          </div>
          <div class="column">
            <div class="login">
              <?php if ($app->security->isLoggedIn()): ?>
              <p>
                Logged in: <a href="<?=sprintf("%susers/details/%d/", $app->getProjectRoot(), $app->security->user->id)?>">
                  <?=$app->security->user->name?></a> (<a href="<?=$app->linkProvider->forLogout()?>">logout</a>).
              </p>
              <?php else: ?>
              <p>Not logged in (<a href="<?=$app->linkProvider->forLogin()?>">login</a>).</p>
              <?php endif ?>
            </div>
            <div class="language">
              <button id="language">
                <span class="text"><?=T_("Language:")?> <?=$app->locale->getCurrent()->name?></span>
                <img src="<?=$app->getImagePath("pixel.gif")?>" class="arrow" />
              </button>
              <div class="menu">
                <?php foreach ($app->locale->getLanguages() as $l): ?>
                <div class="item"><a href="?lang=<?=$l->code?>"><?=$l->name?></a></div>
                <?php endforeach ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="content">
        <?php require $content; ?>
        <div class="clear"></div>
      </div>
      <div class="footer">
        <div class="footerContent">
          <p>
          <?php
            echo sprintf(
              T_("Powered by %s. Copyright &copy; %s 2012. Load time: %s ms. SQL queries: %s."),
              "<a href=\"http://spit-foss.org\">SPIT</a>: Simple PHP Issue Tracker",
              "<a href=\"http://nbolton.net\">Nick Bolton</a>",
              sprintf("<span class=\"loadTime\">%.2f</span>", $app->getLoadTime()),
              sprintf("<span class=\"queries\">%d</span>", $app->queryCount)
            );
          ?>
          </p>
        </div>
      </div>
    </div>
  </body>
</html>
