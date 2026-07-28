<?php
/**
 * Zarzadzanie FAQ przez wlasny typ tresci (CPT) -> schema FAQPage.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_Faq
 */
class CSA_Faq {

	const POST_TYPE = 'csa_faq';

	/**
	 * Konstruktor: rejestruje CPT.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Rejestracja typu tresci "FAQ".
	 *
	 * Tytul wpisu = pytanie, tresc wpisu = odpowiedz.
	 */
	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'FAQ (AI SEO)', 'claude-seo-ai' ),
			'singular_name'      => __( 'Pytanie FAQ', 'claude-seo-ai' ),
			'add_new'            => __( 'Dodaj pytanie', 'claude-seo-ai' ),
			'add_new_item'       => __( 'Dodaj nowe pytanie', 'claude-seo-ai' ),
			'edit_item'          => __( 'Edytuj pytanie', 'claude-seo-ai' ),
			'new_item'           => __( 'Nowe pytanie', 'claude-seo-ai' ),
			'view_item'          => __( 'Zobacz pytanie', 'claude-seo-ai' ),
			'search_items'       => __( 'Szukaj w FAQ', 'claude-seo-ai' ),
			'not_found'          => __( 'Brak pytan', 'claude-seo-ai' ),
			'menu_name'          => __( 'FAQ (AI SEO)', 'claude-seo-ai' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => $labels,
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-editor-help',
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
				'has_archive'  => false,
				'rewrite'      => false,
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Pobiera opublikowane wpisy FAQ jako pary pytanie/odpowiedz.
	 *
	 * @return array
	 */
	public static function get_items() {
		$query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		$items = array();
		foreach ( $query->posts as $post ) {
			$question = wp_strip_all_tags( get_the_title( $post ) );
			$answer   = wp_strip_all_tags( wp_trim_words( $post->post_content, 120, '' ) );
			$answer   = trim( preg_replace( '/\s+/', ' ', $answer ) );

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			$items[] = array(
				'question' => $question,
				'answer'   => $answer,
			);
		}

		wp_reset_postdata();

		return $items;
	}
}
