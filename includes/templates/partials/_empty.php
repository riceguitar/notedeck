<section class="section-empty container">
	<div class="row">
		<div class="1-xs-col">
			<h1 class="nd-title">Looks like there aren't any sections in your note deck!</h1>
			<?php if (current_user_can('manage_options')):?>
			<div class="nd-content">
				<p><?php edit_post_link('Add some sections here!') ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>