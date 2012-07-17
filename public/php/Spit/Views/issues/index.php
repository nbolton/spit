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

<div id="query" style="<?=($self->userCanSeequeryLink() && $query != null ? "display: inherit" : "")?>">
  <span>
    <form action="./" method="post">
      <input name="name" type="text" value="<?=($query != null ? $query->name : "query1")?>" />
      <input name="filter" type="text" value="<?=($query != null ? $query->getFilterEncoded() : "")?>" />
      <input name="order" type="text" value="<?=($query != null ? $query->getOrderEncoded() : "")?>" />
      <input name="query" type="submit" value="Save" />
    </form>
  </span>
</div>

<p>
<?php if ($self->userCanSeeCreateLink()): ?>
<a href="<?=sprintf("%s/issues/new/", $self->app->getProjectRoot())?>"><?=T_("New issue")?></a>
<?php endif ?>
<?php if ($self->userCanSeequeryLink() && $query == null): ?>
<a id="showQuery" href="javascript:void(0)"><?=T_("New query")?></a>
<?php endif ?>
<?php foreach ($queries as $q): ?>
<a href="<?=$self->app->linkProvider->forQuery($q->name)?>"><?=$q->name?></a>
<?php endforeach ?>
</p>

<div id="issues">
  <div class="box">
    <div class="loading">
      <span><img src="<?=$app->getImage("loading.gif")?>" /></span>
    </div>
    <table></table>
  </div>
  <div class="paging">
    <?=T_("Page: ")?>
    <a class="back" href="javascript:void(0)"><?=T_("&laquo; Back")?></a>
    (<span class="page"></span>/<span class="pageCount"></span>)
    <a class="next" href="javascript:void(0)"><?=T_("Next &raquo;")?></a>
  </div>
</div>

<div id="templates">
  <table class="issues">
    <thead>
      <tr>
      </tr>
    </thead>
    <tbody>
      <tr>
      </tr>
    </tbody>
  </table>
</div>
