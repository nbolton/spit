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

namespace Spit\DataStores;

class AttachmentDataStore extends DataStore {

  const BULK_INSERT_MAX = 500;

  public function get() {
    $result = $this->query("select * from attachment");
    return $this->fromResult($result);
  }

  public function getForIssue($issueId) {
    $result = $this->query("select * from attachment where issueId = %d", (int)$issueId);
    return $this->fromResult($result);
  }
  
  public function getImportIds() {
    $result = $this->query("select id, importId from attachment");
    return $this->fromResult($result);
  }
  
  public function insertMany($attachments) {
    $base =
      "insert into attachment " .
      "(importId, issueId, creatorId, originalName, physicalName, size, contentType, created) values ";
    
    for ($j = 0; $j < count($attachments) / self::BULK_INSERT_MAX; $j++) {
      
      $slice = array_slice($attachments, $j * self::BULK_INSERT_MAX, self::BULK_INSERT_MAX);
      $count = count($slice);
      $values = "";
      
      for ($i = 0; $i < $count; $i++) {
        $attachment = $slice[$i];
        $values .= $this->format(
          "(%s, %d, %s, %s, %s, %d, %s, %s)",
          self::nullInt($attachment->importId),
          (int)$attachment->issueId,
          self::nullInt($attachment->creatorId),
          $attachment->originalName,
          $attachment->physicalName,
          (int)$attachment->size,
          $attachment->contentType,
          $attachment->created)
          .($i < $count - 1 ? ", " : "");
      }
      
      $this->query($base . $values);
    }
  }
  
  public function truncate() {
    $this->query("truncate table attachment");
  }
}

?>
