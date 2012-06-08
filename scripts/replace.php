<?php

function usage() {
  echo "Usage: replace.php --table name --field name --find regex --replace regex";
}

function main($argv, $argc) {
  $context = init($argv, $argc);
  $sql = $context->sql;
  
  $result = $sql->query(sprintf(
    "select id, %s from %s",
    $context->field,
    $context->table
  ));
  
  if ($result == null) {
    return 4;
  }
  
  $field = $context->field;
  $total = 0;
  
  while ($row = $result->fetch_object()) {
    $text = $row->$field;
    
    $count = 0;
    $text = preg_replace($context->find, $context->replace, $text, -1, $count);
    
    if ($count != 0) {
      echo "replacing $count in $row->id \n";
      $text = $sql->escape_string(str_replace("%", "%%", $text));
      $sql->query(sprintf(
        "update %s set %s = \"%s\" where id = %d",
        $context->table,
        $context->field,
        $text,
        $row->id
      ));
    }
    
    $total += $count;
  }
  
  echo "\nreplace total: $total";
  
  return 0;
}

function init($argv, $argc) {
  print "MySQL replace tool (replace.php)\nCopyright (C) 2012 Nick Bolton\n";
  
  if ($argc < 5) {
    echo "not enough args\n";
    usage();
    exit(1);
  }
  
  $context = new stdClass;
  
  for ($i = 1; $i < $argc - 1; $i++) {
    $arg = $argv[$i];
    
    switch ($arg) {
      case "--table": $context->table = $argv[++$i]; break;
      case "--field": $context->field = $argv[++$i]; break;
      case "--find": $context->find = $argv[++$i]; break;
      case "--replace": $context->replace = $argv[++$i]; break;
      default: echo "invalid arg: $arg\n"; usage(); exit(2);
    }
  }

  print sprintf(
    "\noptions:\n  table: %s\n  field: %s\n  find: %s\n  replace: %s\n",
    $context->table,
    $context->field,
    $context->find,
    $context->replace
  );

  if (!file_exists("settings.ini")) {
    echo "missing: settings.ini";
    exit(3);
  }

  $ini = parse_ini_file("settings.ini", true);
  $host = $ini["db"]["host"];
  $user = $ini["db"]["user"];
  $password = $ini["db"]["password"];
  $database = $ini["db"]["database"];
  
  $hasPassword = $password != null ? "yes" : "no";
  print "\ndatabase:\n  host: $host \n  user: $user \n  password: $hasPassword \n  database: $database \n";
  
  $sql = new mysqli($host, $user, $password, $database);  
  if ($sql->connect_errno) {
    throw new Exception("failed to connect to mysql: " . $sql->connect_error);
  }
  $sql->set_charset("utf8");
  $context->sql = $sql;
  
  return $context;
}

exit(main($argv, $argc));

?>
