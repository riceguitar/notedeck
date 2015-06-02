<?php 
$background = '#ffffff;';
$text_color = '#000000';
$notes_area = '';
if(get_sub_field('background_type') == 'Solid Color') 
{
	$background = get_sub_field('background_color') . ';';
} 
else if(get_sub_field('background_type') == 'Background Image')
{
	$background = 'url(' . get_sub_field('background_image') . ') no-repeat center center;';
}
if ( get_sub_field('text_content_color') )
{
	$text_color = get_sub_field('text_content_color');
}

?>

<section class="nd-section nd-section-text" style="background: <?php echo $background;?>">
	<div class="container">
		<div class="nd-text-content" style="color: <?php echo $text_color; ?>;">
			<?php the_sub_field('text_content'); ?>		
		</div>
		<?php if ( get_sub_field('notes_button')):?>
		<button class="add-notes-button">
			<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> COPY TO NOTES
		</button>
		<?php endif; ?>
	</div>
</section>
