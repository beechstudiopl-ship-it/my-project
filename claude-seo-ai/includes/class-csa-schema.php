<?php
/**
 * Wstrzykiwanie danych strukturalnych (JSON-LD) do <head>.
 *
 * @package ClaudeSeoAi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CSA_Schema
 */
class CSA_Schema {

	/**
	 * Konstruktor: podpina wypis do naglowka.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_organization' ), 20 );
		add_action( 'wp_head', array( $this, 'output_faq' ), 21 );
	}

	/**
	 * Bezpieczny wypis bloku JSON-LD.
	 *
	 * @param array $data Dane do zakodowania.
	 */
	public static function print_jsonld( $data ) {
		$data = array_filter(
			$data,
			static function ( $value ) {
				return '' !== $value && array() !== $value && null !== $value;
			}
		);

		echo "\n<script type=\"application/ld+json\">\n";
		echo wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}

	/**
	 * Dane firmy (Organization / LocalBusiness / EducationalOrganization).
	 */
	public function output_organization() {
		$s = csa_get_settings();

		if ( empty( $s['enable_org'] ) || empty( $s['name'] ) ) {
			return;
		}

		$data = array(
			'@context'    => 'https://schema.org',
			'@type'       => $s['org_type'] ? $s['org_type'] : 'Organization',
			'name'        => $s['name'],
			'url'         => home_url( '/' ),
			'description' => $s['description'],
		);

		if ( ! empty( $s['alternate_name'] ) ) {
			$data['alternateName'] = $s['alternate_name'];
		}
		if ( ! empty( $s['logo'] ) ) {
			$data['logo']  = $s['logo'];
			$data['image'] = $s['logo'];
		}
		if ( ! empty( $s['email'] ) ) {
			$data['email'] = $s['email'];
		}
		if ( ! empty( $s['telephone'] ) ) {
			$data['telephone'] = $s['telephone'];
		}

		$address = array_filter(
			array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $s['street'],
				'postalCode'      => $s['postal_code'],
				'addressLocality' => $s['city'],
				'addressRegion'   => $s['region'],
				'addressCountry'  => $s['country'],
			),
			static function ( $v ) {
				return '' !== $v;
			}
		);
		if ( count( $address ) > 1 ) {
			$data['address'] = $address;
		}

		$areas = $this->split_list( $s['area_served'] );
		if ( ! empty( $areas ) ) {
			$data['areaServed'] = $areas;
		}

		$same_as = $this->split_list( $s['same_as'] );
		if ( ! empty( $same_as ) ) {
			$data['sameAs'] = $same_as;
		}

		$catalog = $this->build_offer_catalog();
		if ( ! empty( $catalog ) ) {
			$data['hasOfferCatalog'] = $catalog;
		}

		self::print_jsonld( $data );
	}

	/**
	 * Buduje OfferCatalog ze wszystkich stron/wpisow oznaczonych jako kurs
	 * (metabox "Ta strona opisuje kurs" w CSA_Course), zeby Google i boty AI
	 * widzialy pelna oferte szkolen w jednym miejscu.
	 *
	 * @return array
	 */
	private function build_offer_catalog() {
		$query = new WP_Query(
			array(
				'post_type'      => array( 'page', 'post' ),
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'no_found_rows'  => true,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_key'       => CSA_Course::META_ENABLED, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( empty( $query->posts ) ) {
			return array();
		}

		$list = array();
		foreach ( $query->posts as $post ) {
			$list[] = array(
				'@type' => 'Course',
				'name'  => wp_strip_all_tags( get_the_title( $post ) ),
				'url'   => get_permalink( $post ),
			);
		}

		return array(
			'@type'           => 'OfferCatalog',
			'name'            => __( 'Szkolenia i kursy', 'claude-seo-ai' ),
			'itemListElement' => $list,
		);
	}

	/**
	 * Dane FAQ (FAQPage) zbudowane z wpisow CPT.
	 */
	public function output_faq() {
		$s = csa_get_settings();

		if ( empty( $s['enable_faq'] ) ) {
			return;
		}

		$global_scope_matches = ( 'all' === $s['faq_scope'] ) || ( 'front' === $s['faq_scope'] && is_front_page() );

		$items = $global_scope_matches ? CSA_Faq::get_items() : array();

		// Niezaleznie od zasiegu globalnego: pytania przypisane wprost do biezacej strony.
		if ( is_singular() ) {
			$current_path = wp_parse_url( get_permalink(), PHP_URL_PATH );
			$page_items   = $current_path ? CSA_Faq::get_items( $current_path ) : array();
			$known        = wp_list_pluck( $items, 'question' );
			foreach ( $page_items as $page_item ) {
				if ( ! in_array( $page_item['question'], $known, true ) ) {
					$items[] = $page_item;
				}
			}
		}

		if ( empty( $items ) ) {
			return;
		}

		$entities = array();
		foreach ( $items as $item ) {
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['answer'],
				),
			);
		}

		self::print_jsonld(
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $entities,
			)
		);
	}

	/**
	 * Zamienia liste rozdzielona przecinkami/nowymi liniami na tablice.
	 *
	 * @param string $raw Surowy tekst.
	 * @return array
	 */
	private function split_list( $raw ) {
		if ( empty( $raw ) ) {
			return array();
		}
		$parts = preg_split( '/[\r\n,]+/', $raw );
		$parts = array_map( 'trim', $parts );
		$parts = array_filter( $parts );

		return array_values( $parts );
	}
}
