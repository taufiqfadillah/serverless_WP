<?php
/**
 * Custom functions that act on comments.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Sober
 */

/**
 * Remove Website field from comment form
 *
 * @param array $fields Default comment fields.
 *
 * @return array
 */
function sober_disable_comment_url( $fields ) {
	unset( $fields['url'] );

	return $fields;
}

add_filter( 'comment_form_default_fields', 'sober_disable_comment_url' );


/**
 * Template Comment
 *
 * @since  1.0
 *
 * @param  object $comment The comment object.
 * @param  array  $args    Comment args.
 * @param  int    $depth   The depth of the comment.
 *
 * @return mixed
 */
function sober_comment( $comment, $args, $depth ) {
	// phpcs:disable
	$GLOBALS['comment'] = $comment;
	extract( $args, EXTR_SKIP );
	// phpcs:enable

	if ( 'div' == $args['style'] ) {
		$add_below = 'comment';
	} else {
		$add_below = 'div-comment';
	}
	?>

<li <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
	<article id="div-comment-<?php comment_ID(); ?>" class="comment-body clearfix">

		<div class="comment-author vcard">
			<?php
			if ( 0 != $args['avatar_size'] ) {
				echo get_avatar( $comment, $args['avatar_size'] );
			}
			?>
		</div>
		<div class="comment-meta commentmetadata">
			<?php printf( '<cite class="author-name">%s</cite>', get_comment_author_link() ); ?>

			<a class="author-posted" href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
				<?php echo esc_html( get_comment_date( 'F d, Y' ) ); ?>
			</a>

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'sober' ); ?></em>
			<?php endif; ?>

			<div class="comment-content">
				<?php comment_text(); ?>
			</div>

			<?php
			comment_reply_link(
				array_merge(
					$args,
					array(
						'add_below'  => $add_below,
						'depth'      => $depth,
						'max_depth'  => $args['max_depth'],
						'reply_text' => esc_html__( 'Reply', 'sober' ),
					)
				)
			);
			edit_comment_link( esc_html__( 'Edit', 'sober' ), '  ', '' );
			?>
		</div>
	</article>

	<?php
}
