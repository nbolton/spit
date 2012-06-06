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

<h2><?=T_("Hello World")?></h2>
<p>
<?php
echo sprintf(
  T_("Welcome to %s, a free and open source issue tracker by %s."),
  "<a href=\"http://spit-foss.org\">SPIT</a> (Simple PHP Issue Tracker)",
  "Nick Bolton"
);
?>
</p>
<p><a href="issues/"><?=T_("View issues")?></a></p>
