<?php 
$image = get_sub_field('image');
$background = '#ffffff';
$link_open = '';
$link_close = '';
if ( get_sub_field('image_link') )
{
	$link_open = '<a href="' . get_sub_field('image_link') . '">';
	$link_close = '</a>';
}
if( get_sub_field('image_background') ) 
{
	$background = get_sub_field('image_background') . ';';
} 
?>

<section class="nd-section nd-section-image" style="background: <?php echo $background;?>">
		<div class="nd-wrapper row">
			<?php echo $link_open ?>
			<img class="nd-image" src="<?php echo $image ?>"/>
			<?php echo $link_close ?>
		</div>
</section>