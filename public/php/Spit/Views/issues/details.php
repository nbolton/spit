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

<h3><?=$title?></h3>
<div class="box">
  <?php if($self->userCanEdit($issue, true)): ?>
  <a href="<?=$app->linkProvider->forIssueEdit($issue->id)?>">
    <img src="<?=$app->getImage("edit.png")?>"/>Edit
  </a>
  
  <hr />
  <?php endif ?>
  <?php foreach($columns as $column): ?>
  <div class="column">
    <?php foreach($column as $field): ?>
      <div class="row">
        <div class="label"><?=$field->label?></div>
        <div class="value"><?=$field->value?></div>
      </div>
    <?php endforeach ?>
  </div>
  <?php endforeach ?>
  
  <hr />
  <span class="relations">
    <?php if ($self->canCreateRelation()): ?>
    <div class="add">
      <p><a class="add" href="javascript:void(0)"><?=T_("Add relation")?></a></p>
      <div class="form">
        <select name="type">
          <option value="<?=\Spit\Models\RelationType::Generic?>"><?=T_("Related to")?></option>
          <option value="<?=\Spit\Models\RelationType::Duplicates?>:l"><?=T_("Duplicates")?></option>
          <option value="<?=\Spit\Models\RelationType::Duplicates?>:r"><?=T_("Duplicated by")?></option>
          <option value="<?=\Spit\Models\RelationType::Blocks?>:l"><?=T_("Blocks")?></option>
          <option value="<?=\Spit\Models\RelationType::Blocks?>:r"><?=T_("Blocked by")?></option>
          <option value="<?=\Spit\Models\RelationType::Follows?>:l"><?=T_("Follows")?></option>
          <option value="<?=\Spit\Models\RelationType::Follows?>:r"><?=T_("Followed by")?></option>
        </select>
        <input type="text" name="issueId" />
        <input type="button" name="add" value="<?=T_("Add")?>" />
        <p><a class="cancel" href="javascript:void(0)"><?=T_("Cancel")?></a></p>
      </div>
      <div class="loading">
        <img src="<?=$app->getImage("loading.gif")?>" />
      </div>
    </div>
    <?php endif ?>
    <ul class="issues">
    <?php foreach($relations as $relation): ?>
      <li><?=$relation->getHtmlInfo($self, $issue->id)?></li>
    <?php endforeach ?>
    </ul>
  </span>
  
  <?php if (trim($issue->details) != ""): ?>
  <hr />
  <span class="details">
    <?=$this->markdown($issue->details)?>
  </span>
  <?php endif ?>
  
  <?php if (count($attachments) != 0): ?>
  <hr />
  <span class="attachments">
    <ul>
    <?php foreach($attachments as $attachment): ?>
      <li><?=$self->getAttachmentInfo($attachment)?></li>
    <?php endforeach ?>
    </ul>
  </span>
  <?php endif ?>
  
  <?php $i = 1; ?>
  <div class="changes">
    <?php foreach($changes as $change): ?>
    <div class="change">
      <hr />
      <a name="c<?=$i?>"></a>
      <?php if ($change->type == \Spit\Models\ChangeType::Comment): ?>
      <div class="anchor"><p>#<?=$i;?></p></div>
      <?php $i++ ?>
      <?php endif ?>
      <span class="info"><?=$self->getChangeInfo($change)?></span>
      <span class="content"><?=$self->getChangeContent($change)?></span>
    </div>
    <?php endforeach ?>
  </div>
</div>

<?php if ($app->security->isLoggedIn()): ?>

<div class="comment">
  <p>
    <a id="writeComment" href="javascript:void(0)">
      <img src="<?=$app->getImage("edit.png")?>"/><?=T_("Write comment")?>
    </a>
  </p>
  <form>
    <div id="wmd-button-bar" class="wmd-button-bar"></div>
    <textarea id="wmd-input" name="content" class="wmd-input"></textarea>
    <input class="button" type="button" value="<?=T_("OK")?>" /> <a id="cancelComment" href="javascript:void(0)">Cancel</a>
    <div class="preview">
      <div id="wmd-preview" class="box"></div>
    </div>
  </form>
  <div class="loading">
    <img src="<?=$app->getImage("loading.gif")?>" />
  </div>
</div>

<?php if(isset($_GET["comment"])): ?>
<script type="text/javascript">
showCommentsBox();
</script>
<?php endif ?>

<?php else: ?>

<div class="comment">
  <p><a href="?comment"><img src="<?=$app->getImage("edit.png")?>"/><?=T_("Write comment")?></a></p>
</div>

<?php endif ?>

<div id="templates">
  <div class="change">
    <hr />
    <span class="info"></span>
    <span class="content"></span>
  </div>
</div>
