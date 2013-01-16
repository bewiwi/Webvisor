<?php
include('include.php');

?>
<html>
	<head>
        <title>Webvisor</title>
        
        <?php foreach (glob("web/js/*.js") as $filename): ?>
        <script type="text/javascript" src="<?php echo $filename ?>"></script>
        <?php endforeach ?>
        
        <?php foreach (glob("web/css/*.css") as $filename): ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $filename ?>" />
        <?php endforeach ?>
	</head>
	<body>   
        <!-- NAV BAR-->
        <div class="navbar  navbar-top">
            <div class="navbar-inner">
                <div class="container-fluid">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="#">Webvisor</a>
                    <div class="nav-collapse collapse">
                        <p class="navbar-text pull-right">
                          Logged in as <a href="#" class="navbar-link"><?php echo $_SERVER['PHP_AUTH_USER'] ?></a>
                        </p>
                        <ul class="nav">
                          <li class="active"><a href="index.php">Home</a></li>
                          <li><a href="#about">About</a></li>
                          <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>
        
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span3 bs-docs-sidebar" id='Menu'>
                     <!--Sidebar content-->
                    <?php //displayMenu(); ?>
                    <script type="text/javascript" >
                        var data= {actionajax : 'displayAjaxFunction', phpfunction : 'displayMenu'};
                        var el = document.getElementById('Menu');
                        displayAjaxFunction(el,data);
                    </script>
                </div>
                <div class="span9">
                    <!--Body content-->
                    <?php include 'internal/routing.php'; ?>
                </div>
            </div>
        </div>
    </body>
</html>