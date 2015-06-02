<?php
$current_slide_id = $current_slide_id + 1;
$slider_id = 'nd-slider-' . $current_slide_id;
$slider_speed = 4000;
$background = '#ffffff';
$caption = '';
if ( get_sub_field('slider_speed') ) {
	$slider_speed = get_sub_field('slider_speed');
}
if ( get_sub_field('slider_background') ) {
	$background = get_sub_field('slider_background');
}
?>

<section class="nd-section nd-section-slider" style="background: <?php echo $background ?>">
<?php if( have_rows('slider_item') ): ?> 
    <div class="container">
        <div>
            <ul id="<?php echo $slider_id; ?>">
    <?php while( have_rows('slider_item') ): the_row(); ?>

    	<?php if( get_sub_field('slider_url') ): ?>
            <li>
            <div>
    			<a class="nd-slide"href="<?php  the_sub_field('slider_url') ?>">
    	    		<img src="<?php  the_sub_field('slider_image') ?>"/>
    	    	</a>
            </div>
            </li>
    	<?php else:?>
            <li class="nd-slide-wrap">
                <img class="nd-slide" src="<?php  the_sub_field('slider_image') ?>"/>
            </li>
    	<?php endif; ?>
    	
	    <?php endwhile; ?>
            </ul>
        </div>
    </div>
 <?php else: ?>
 	<h3>Add slides to the slider.</h3>
<?php endif; ?>
</section>
<script>
$(document).ready(function() {
    $("<?php echo '#' . $slider_id; ?>").lightSlider({
        pause: <?php echo $slider_speed; ?>,
        adaptiveHeight: true,
        item: 1,
        auto: true,
        loop: true,
        slideMargin: 0,
        pager: false,
    }); 
  });
</script>