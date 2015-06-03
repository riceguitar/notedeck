<?php
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

require_once( reset(explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] )) . 'wp-load.php' );

$email_message = $_POST['content'];
$email_footer = '


--------------

This message was sent via NoteDeck from ' . get_bloginfo( 'name' ) . '
' . get_site_url();

$email = $_POST['email'];
$content = $email_message . $email_footer;
$subject = get_field('notes_email_subject', 'option');
$from = get_field('notes_email_from', 'option');
$from = ($from == '') ? get_option('admin_email') : $from;
$name = get_field('notes_email_name', 'option');
$name = ($name == '') ? 'NoteDeck' : $name;
$headers = 'From: ' . $name . ' <' . $from . '>' . "\r\n";

$check = wp_mail( $email, $subject, $content, $headers );

echo $check;
?>