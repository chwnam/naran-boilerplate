<?php
get_header();

while ( have_posts() ) {
	the_post();
	?>
    <article <?php post_class(); ?>>
		<?php the_title( '<h2>', '</h2>' ); ?>
        <div>
			<?php the_content(); ?>
        </div>
    </article>
	<?php
}

get_footer();
