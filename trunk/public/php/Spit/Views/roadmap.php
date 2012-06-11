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

<h2><?=$title?></h2>

<?php foreach($versions as $version): ?>
<div class="version box">
  <h3><?=$version->name?></h3>
  <?php if ($version->releaseDate != null): ?>
  <p class="date"><?=$version->getDateInfo($self->app->dateFormatter)?></p>
  <? endif ?>
  <div class="progress">
    <div class="bar">
      <div class="complete" style="width: <?=$version->getProgressPercent()?>%"></div>
    </div>
    <div class="label">
      <p><?=$version->getProgressPercent()?>%</p>
    </div>
  </div>
  <ul class="issues">
    <?php foreach($version->issues as $issue): ?>
    <li><?=$issue->getHtmlInfo($app->linkProvider)?></li>
    <?php endforeach ?>
  </ul>
</div>
<?php endforeach ?>
