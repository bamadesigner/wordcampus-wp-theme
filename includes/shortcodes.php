<?php

/**
 * We have to remove the default shortcode filter.
 *
 * Runs AFTER wpautop().
 */
remove_filter( 'the_content', 'do_shortcode', 11 );

/**
 * Strips extra space around and within shortcodes.
 */
add_filter( 'the_content', function( $content ) {

	// Clean it.
	$content = strtr( $content, array( "\n[" => '[', "]\n" => ']', '<p>[' => '[', ']</p>' => ']', ']<br>' => ']', ']<br />' => ']' ) );

	// Return the content.
	return do_shortcode( $content );

}, 11 );

/**
 * Include columns in content.
 */
add_shortcode( 'columns', function( $args, $content = null ) {

	// Make sure there's content to wrap.
	if ( ! $content ) {
		return null;
	}

	// Process for more levels of shortcode, wrap in row and return.
	return '<div class="row">' . do_shortcode( $content ) . '</div>';

});

/**
 * Include columns in content.
 */
add_shortcode( 'col', function( $args, $content = null ) {

	// Make sure there's content to wrap.
	if ( ! $content ) {
		return null;
	}

	// Process args.
	$defaults = array(
		'small'     => '12',
		'medium'    => false,
		'large'     => false,
	);
	$args = wp_parse_args( $args, $defaults );

	// Setup column classes.
	$column_classes = array();

	foreach ( array( 'small', 'medium', 'large' ) as $size ) {

		// If a value was passed, make sure its a number.
		if ( isset( $args[ $size ] ) && ! is_numeric( $args[ $size ] ) && ! is_int( $args[ $size ] ) ) {
			continue;
		}

		// Add the class.
		$column_classes[] = $args[ $size ] . '-' . $args[ $size ];

	}

	return '<div class="' . implode( ' ', $column_classes ) . ' columns">' . do_shortcode( $content ) . '</div>';
});

/**
 * Return WPCampus data.
 */
add_shortcode( 'wpcampus_data', function( $args, $content = null ) {

	// Process args.
	$defaults = array(
		'set'       => null,
		'format'    => 'number', // Other options: percent, both
	);
	$args = wp_parse_args( $args, $defaults );

	// Build the content.
	$content = null;

	switch ( $args['set'] ) {

		case 'no_of_interested':
			return wpcampus_get_involved_count();

		case 'attend_in_person':
			return format_wpcampus_data_set( wpcampus_get_attend_in_person_count(), $args['format'] );

		case 'attend_has_location':
			return format_wpcampus_data_set( wpcampus_get_interested_has_location_count(), $args['format'] );

		case 'attend_live_stream':
			return format_wpcampus_data_set( wpcampus_get_attend_live_stream_count(), $args['format'] );

		case 'work_in_higher_ed':
			return format_wpcampus_data_set( wpcampus_get_work_in_higher_ed_count(), $args['format'] );

		case 'work_for_company':
			return format_wpcampus_data_set( wpcampus_get_work_for_company_count(), $args['format'] );

		case 'work_outside_higher_ed':
			return format_wpcampus_data_set( wpcampus_get_work_outside_higher_ed_count(), $args['format'] );

		case 'group_attending':
		case 'group_hosting':
		case 'group_planning':
		case 'group_speaking':
		case 'group_sponsoring':
			return format_wpcampus_data_set( wpcampus_get_group_count( preg_replace( '/^group\_/i', '', $args['set'] ) ), $args['format'] );

		case 'no_of_votes_on_new_name':
			return format_wpcampus_data_set( wpcampus_get_vote_on_new_name_count() );

	}

	return $content;

});

function format_wpcampus_data_set( $count, $format = 'number' ) {

	switch ( $format ) {

		case 'number':
		case 'both':
			$number = $count;

			if ( 'number' == $format ) {
				return $number;
			}

		case 'percent':
		case 'both':

			// Get total.
			$total = wpcampus_get_involved_count();

			// Add percentage.
			$percent = round( ( $count / $total ) * 100 ) . '%';

			if ( 'percent' == $format ) {
				return $percent;
			}

			return "{$number} ({$percent})";

		default:
			return $count;

	}

}
