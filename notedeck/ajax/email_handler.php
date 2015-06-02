<?php

require( '../../../../wp-load.php' );

$email = $_POST['email'];
$content = $_POST['content'];
$subject = get_field('notes_email_subject', 'option');
$from = get_field('notes_email_from', 'option');
$from = ($from == '') ? get_option('admin_email') : $from;
$name = get_field('notes_email_name', 'option');
$name = ($name == '') ? 'NoteDeck' : $name;
$headers = 'From: ' . $name . ' <' . $from . '>' . "\r\n";

$check = wp_mail( $email, $subject, $content, $headers );

echo $check;
?>