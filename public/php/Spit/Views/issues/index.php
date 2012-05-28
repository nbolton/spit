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
<p><a href="new/"><?=T_("New issue")?></a></p>

<div id="issues">
  <div class="box">
    <div class="loading">
      <span><img src="<?=$app->getThemeRoot()?>/image/loading.gif" /></span>
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
        <th class="checkbox"><a href="javascript:void(0)"><img src="<?=$app->getImage("toggle_check.png")?>"></a></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="checkbox"><input name="id" type="checkbox"></td>
      </tr>
    </tbody>
  </table>
</div>
