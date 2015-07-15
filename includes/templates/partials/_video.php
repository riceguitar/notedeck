<?php
$background = '#ffffff';
$embed_code = $noteDeck->getVideoPlayer(
	get_sub_field('video_url'),
	get_sub_field('video_type')
);
if( get_sub_field('video_background') ) 
{
	$background = get_sub_field('video_background') . ';';
} 
?>

<section class="nd-section nd-section-video" style="background: <?php echo $background;?>">
	<div class="container">
		<div class="nd-wrapper row">
			<?php echo $embed_code?>
		</div>
	</div>
</section>