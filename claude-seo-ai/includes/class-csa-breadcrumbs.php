<?php
/**
 * Schema BreadcrumbList dla podstron.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_Breadcrumbs
 */
class CSA_Breadcrumbs {

	/**
	 * Konstruktor: podpina wypis do naglowka.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_breadcrumbs' ), 23 );
	}

	/**
	 * Wypisuje BreadcrumbList na podstronach (nie na stronie glownej).
	 */
	public function output_breadcrumbs() {
		$s = csa_get_settings();

		if ( empty( $s['enable_breadcrumbs'] ) ) {
			return;
		}

		if ( ! is_singular() || is_front_page() ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$trail   = array();
		$trail[] = array(
			'name' => __( 'Strona główna', 'claude-seo-ai' ),
			'url'  => home_url( '/' ),
		);

		$ancestors = array_reverse( get_post_ancestors( $post_id ) );
		foreach ( $ancestors as $ancestor_id ) {
			$trail[] = array(
				'name' => wp_strip_all_tags( get_the_title( $ancestor_id ) ),
				'url'  => get_permalink( $ancestor_id ),
			);
		}

		$trail[] = array(
			'name' => wp_strip_all_tags( get_the_title( $post_id ) ),
			'url'  => get_permalink( $post_id ),
		);

		$items = array();
		foreach ( $trail as $position => $step ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position + 1,
				'name'     => $step['name'],
				'item'     => $step['url'],
			);
		}

		CSA_Schema::print_jsonld(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			)
		);
	}
}
