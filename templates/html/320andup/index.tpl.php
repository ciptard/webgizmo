<!DOCTYPE html>

<!-- 
320 and Up boilerplate extension
Author: Andy Clarke
Version: 0.9b
URL: http://stuffandnonsense.co.uk/projects/320andup 
-->

<!--[if IEMobile 7 ]><html class="no-js iem7" manifest="default.appcache?v=1"><![endif]-->
<!--[if lt IE 7 ]><html class="no-js ie6" lang="en"><![endif]-->
<!--[if IE 7 ]><html class="no-js ie7" lang="en"><![endif]-->
<!--[if IE 8 ]><html class="no-js ie8" lang="en"><![endif]-->
<!--[if (gte IE 9)|(gt IEMobile 7)|!(IEMobile)|!(IE)]><!--><html class="no-js" manifest="default.appcache?v=1" lang="en"><!--<![endif]-->

<head>
	<meta charset="utf-8">

	<title>320 and up</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- http://t.co/dKP3o1e -->
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, target-densitydpi=160dpi, initial-scale=1">

	<!-- For less capable mobile browsers
	<link rel="stylesheet" media="handheld" href="css/handheld.css?v=1">  -->

	<!-- For all browsers -->
	<link rel="stylesheet" media="screen" href="css/style.css?v=1">
	<link rel="stylesheet" media="print" href="css/print.css?v=1">
	<!-- For progressively larger displays -->
	<link rel="stylesheet" media="only screen and (min-width: 480px)" href="css/480.css?v=1">
	<link rel="stylesheet" media="only screen and (min-width: 768px)" href="css/768.css?v=1">
	<link rel="stylesheet" media="only screen and (min-width: 992px)" href="css/992.css?v=1">
	<link rel="stylesheet" media="only screen and (min-width: 1382px)" href="css/1382.css?v=1">
	<!-- For Retina displays -->
	<link rel="stylesheet" media="only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min-device-pixel-ratio: 2)" href="css/2x.css?v=1">

	<!-- JavaScript at bottom except for Modernizr -->
	<script src="js/libs/modernizr-1.7.min.js"></script>

	<!-- For iPhone 4 -->
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/h/apple-touch-icon.png">
	<!-- For iPad 1-->
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/m/apple-touch-icon.png">
	<!-- For iPhone 3G, iPod Touch and Android -->
	<link rel="apple-touch-icon-precomposed" href="img/l/apple-touch-icon-precomposed.png">
	<!-- For Nokia -->
	<link rel="shortcut icon" href="img/l/apple-touch-icon.png">
	<!-- For everything else -->
	<link rel="shortcut icon" href="/favicon.ico">

	<!--iOS. Delete if not required -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<link rel="apple-touch-startup-image" href="img/splash.png">

	<!--Microsoft. Delete if not required -->
	<meta http-equiv="cleartype" content="on">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<!-- http://t.co/y1jPVnT -->
	<link rel="canonical" href="/">
	
	<?php echo $head ?>
</head>

<body class="clearfix <?php echo $fs->pathCSS() ?>">

	<header role="banner" class="clearfix">
		<!-- Site title, good for SEO if between <H1> tags -->
		<h1><a href="<?php echo $home ?>"><?php echo $title ?></a></h1>
	</header>

	<div class="content clearfix">

		<div role="main">
			<?php echo $content ?>
		</div>

		<div role="complementary">
			<!-- 
				This is the Menu, a list of links
				menu(<default depth to show>, <add the full current path>)
			-->
			<?php echo $fs->menu(1, true) ?>
		</div>

	</div>

	<footer role="contentinfo" class="clearfix">
		<?php echo $foot ?>
	</footer>

	<!-- mathiasbynens.be/notes/async-analytics-snippet Change UA-XXXXX-X to be your site's ID -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.js"></script>
	<script>window.jQuery || document.write('<script src="js/libs/jquery-1.5.1.min.js">\x3C/script>')</script>
	<!-- Scripts -->
	<script src="js/plugins.js"></script>
	<script src="js/script.js"></script>

	<!--[if (lt IE 9) & (!IEMobile)]>
	<script src="js/libs/DOMAssistantCompressed-2.8.js"></script>
	<script src="js/libs/selectivizr-1.0.1.js"></script>
	<script src="js/libs/respond.min.js"></script>
	<![endif]-->

	<!-- http://t.co/HZe9oJ4 -->
	<script>
	var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']]; // Change UA-XXXXX-X to be your site's ID
	(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
	g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
	s.parentNode.insertBefore(g,s)}(document,'script'));
	</script>
	<noscript>Your browser does not support JavaScript!</noscript>
</body>
</html>