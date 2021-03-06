<?php $fonts = GoogleFonts::getInstance(); ?>

<!DOCTYPE HTML>
<html>
<head>
	<title><?php wp_title(); ?></title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
	<script src="<?php echo ND_95W_PLUGIN_URL . 'js/min/lightslider.min.js';?>"></script>

	<link rel="stylesheet" href="<?php echo ND_95W_PLUGIN_URL . 'includes/bootstrap/css/bootstrap.min.css';?>" type="text/css" media="all">
	<link rel="stylesheet" href="<?php echo ND_95W_PLUGIN_URL . 'css/lightslider.min.css';?>" type="text/css" media="all">
	<link rel="stylesheet" href="<?php echo ND_95W_PLUGIN_URL . 'css/main.css';?>"/>
	<link rel="stylesheet" href="<?php echo $fonts->makeUrlString(); ?>"/>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
</head>
<body <?php body_class(); ?>>