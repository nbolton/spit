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
