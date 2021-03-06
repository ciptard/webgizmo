<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">

<html lang="<?php echo $language ?>">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHAR_ENCODING ?>">
	<meta name="generator" content="Web Gizmo <?php echo $gizmo_version ?>">
	
	<title><?php echo $pagetitle ?>, <?php echo $title ?></title>
	
	<!-- IMPORTANT! This is always required between the <HEAD> tags. Plugins 
		use this to put references to CSS and Javascript files as they need them -->
	<?php echo $head ?>
	
</head>

		<!-- This turns the current path into CSS classes to make Page specific CSS easier. -->
<body class="<?php echo $fs->pathCSS() ?>">
	
	<h1>
		<!-- Site title, good for SEO if between <H1> tags -->
		<a href="<?php echo $home ?>"><?php echo $title ?></a>
	</h1>
	
	<!-- 
		This is the Menu, a list of links
		menu(<default depth to show>, <add the full current path>)
	-->
	<?php echo $fs->menu(2, true) ?>
	
	
	<!-- The main contents of the current folder comes out here. -->
	<?php echo $content ?>
	
	
	<!-- This is also always required, juts before the final </BODY> tag. -->
	<?php echo $foot ?>
	
</body>
</html>