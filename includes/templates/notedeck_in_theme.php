<?php
get_header();
$current_slide_id = 0;
// check if the flexible content field has rows of data
?>
    <div id="note-deck-95w">
<?php
if( have_rows('note_deck') ):

     // loop through the rows of data
    while ( have_rows('note_deck') ) : the_row();

        if( get_row_layout() == 'video_section' ):

        	include 'partials/_video.php';

        elseif( get_row_layout() == 'image_section' ): 

        	include 'partials/_image.php';

        elseif( get_row_layout() == 'text_section'):

        	include 'partials/_text.php';

    	elseif( get_row_layout() == 'slider_section'):

    		include 'partials/_slider.php';

        endif;

    endwhile;

else :

	include 'partials/_empty.php';

endif;
?>
<button class="nd-notes-button">
    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
</button>
<?php
include 'partials/_notes.php';
?>
</div>
<?php
include 'partials/_notes.php';
include 'partials/_scripts.php';
get_footer();