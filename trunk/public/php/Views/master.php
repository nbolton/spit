<html>
  <head>
    <title>SPIT</title>
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta name="description" content="SPIT: Simple PHP Issue Tracker" />
    <link rel="stylesheet" type="text/css" href="<?=$root?>/theme/default/main.css" />
  </head>
  <body>
    <div class="layout">
      <div class="header">
        <div class="headerContent">
          <h1>SPIT</h1>
          <p>Simple PHP Issue Tracker</p>
          <div class="links">
            <a href="<?=$root?>/">Home</a>,
            <a href="<?=$root?>/issues/">Issues</a>
          </div>
        </div>
      </div>
      <div class="content">
        <?php require $content . ".php"; ?>
      </div>
      <div class="footer">
        <div class="footerContent">
          <p>Copyright &copy; Nick Bolton 2012</p>
        </div>
      </div>
    </div>
  </body>
</html>
