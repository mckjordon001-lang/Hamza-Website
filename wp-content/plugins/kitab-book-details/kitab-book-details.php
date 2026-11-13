<?php
/**
 * Plugin Name:       Kitab Book Details
 * Description:       Turns WooCommerce products into books: author taxonomy, Urdu title, ISBN, publisher, format, language and page count, plus author/ISBN search.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Text Domain:       kitab-book-details
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

final class Kitab_Book_Details {

	const TAXONOMY = 'book_author';

	/**
	 * Meta key => label for the "Book details" fields.
	 */
	public static function fields() {
		return array(
			'_kitab_title_urdu'  => __( 'Title in Urdu', 'kitab-book-details' ),
			'_kitab_isbn'        => __( 'ISBN', 'kitab-book-details' ),
			'_kitab_publisher'   => __( 'Publisher', 'kitab-book-details' ),
			'_kitab_year'        => __( 'Publication year', 'kitab-book-details' ),
			'_kitab_pages'       => __( 'Pages', 'kitab-book-details' ),
			'_kitab_language'    => __( 'Language', 'kitab-book-details' ),
			'_kitab_format'      => __( 'Format', 'kitab-book-details' ),
		);
	}

	public static function formats() {
		return array(
			''          => __( '— Select —', 'kitab-book-details' ),
			'Paperback' => __( 'Paperback', 'kitab-book-details' ),
			'Hardcover' => __( 'Hardcover', 'kitab-book-details' ),
			'eBook'     => __( 'eBook', 'kitab-book-details' ),
			'Audiobook' => __( 'Audiobook', 'kitab-book-details' ),
		);
	}

	public static function languages() {
		return array(
			''        => __( '— Select —', 'kitab-book-details' ),
			'English' => __( 'English', 'kitab-book-details' ),
			'Urdu'    => __( 'Urdu', 'kitab-book-details' ),
			'Arabic'  => __( 'Arabic', 'kitab-book-details' ),
			'Punjabi' => __( 'Punjabi', 'kitab-book-details' ),
			'Sindhi'  => __( 'Sindhi', 'kitab-book-details' ),
			'Pashto'  => __( 'Pashto', 'kitab-book-details' ),
		);
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );

		// Admin: product data tab.
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'admin_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'admin_panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'admin_save' ) );

		// Front end.
		add_action( 'woocommerce_shop_loop_item_title', array( __CLASS__, 'loop_meta' ), 20 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_meta' ), 6 );
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'product_tab' ) );
		add_filter( 'posts_search', array( __CLASS__, 'extend_search' ), 10, 2 );
		add_filter( 'woocommerce_structured_data_product', array( __CLASS__, 'structured_data' ), 10, 2 );
	}

	public static function register_taxonomy() {
		register_taxonomy(
			self::TAXONOMY,
			'product',
			array(
				'labels'            => array(
					'name'          => __( 'Authors', 'kitab-book-details' ),
					'singular_name' => __( 'Author', 'kitab-book-details' ),
					'search_items'  => __( 'Search authors', 'kitab-book-details' ),
					'all_items'     => __( 'All authors', 'kitab-book-details' ),
					'edit_item'     => __( 'Edit author', 'kitab-book-details' ),
					'add_new_item'  => __( 'Add new author', 'kitab-book-details' ),
					'menu_name'     => __( 'Authors', 'kitab-book-details' ),
					'not_found'     => __( 'No authors found', 'kitab-book-details' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'book-author', 'with_front' => false ),
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Admin
	 * ------------------------------------------------------------------- */

	public static function admin_tab( $tabs ) {
		$tabs['kitab_book'] = array(
			'label'    => __( 'Book details', 'kitab-book-details' ),
			'target'   => 'kitab_book_data',
			'class'    => array(),
			'priority' => 15,
		);
		return $tabs;
	}

	public static function admin_panel() {
		echo '<div id="kitab_book_data" class="panel woocommerce_options_panel"><div class="options_group">';

		woocommerce_wp_text_input( array( 'id' => '_kitab_title_urdu', 'label' => __( 'Title in Urdu', 'kitab-book-details' ), 'placeholder' => 'پیرِ کامل', 'custom_attributes' => array( 'dir' => 'rtl', 'lang' => 'ur' ) ) );
		woocommerce_wp_text_input( array( 'id' => '_kitab_isbn', 'label' => __( 'ISBN', 'kitab-book-details' ), 'placeholder' => '978-…' ) );
		woocommerce_wp_text_input( array( 'id' => '_kitab_publisher', 'label' => __( 'Publisher', 'kitab-book-details' ) ) );
		woocommerce_wp_text_input( array( 'id' => '_kitab_year', 'label' => __( 'Publication year', 'kitab-book-details' ), 'type' => 'number', 'custom_attributes' => array( 'min' => 1000, 'max' => 2100 ) ) );
		woocommerce_wp_text_input( array( 'id' => '_kitab_pages', 'label' => __( 'Pages', 'kitab-book-details' ), 'type' => 'number', 'custom_attributes' => array( 'min' => 1 ) ) );
		woocommerce_wp_select( array( 'id' => '_kitab_language', 'label' => __( 'Language', 'kitab-book-details' ), 'options' => self::languages() ) );
		woocommerce_wp_select( array( 'id' => '_kitab_format', 'label' => __( 'Format', 'kitab-book-details' ), 'options' => self::formats() ) );

		echo '<p class="form-field"><em>' . esc_html__( 'Add the author(s) in the "Authors" box in the right sidebar.', 'kitab-book-details' ) . '</em></p>';
		echo '</div></div>';
	}

	/**
	 * @param WC_Product $product
	 */
	public static function admin_save( $product ) {
		// Nonce is verified by WooCommerce before this hook fires.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		foreach ( array_keys( self::fields() ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$product->update_meta_data( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
		// phpcs:enable
	}

	/* ---------------------------------------------------------------------
	 * Front end
	 * ------------------------------------------------------------------- */

	/**
	 * Comma separated, linked author names.
	 */
	public static function authors_html( $product_id, $link = true ) {
		$terms = get_the_terms( $product_id, self::TAXONOMY );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}
		$out = array();
		foreach ( $terms as $term ) {
			$out[] = $link
				? '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>'
				: esc_html( $term->name );
		}
		return implode( ', ', $out );
	}

	public static function urdu_title_html( $product_id, $tag = 'span' ) {
		$urdu = get_post_meta( $product_id, '_kitab_title_urdu', true );
		if ( '' === $urdu ) {
			return '';
		}
		return sprintf( '<%1$s class="book-title-urdu" lang="ur" dir="rtl">%2$s</%1$s>', tag_escape( $tag ), esc_html( $urdu ) );
	}

	public static function loop_meta() {
		global $product;
		echo self::urdu_title_html( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput
		$authors = self::authors_html( $product->get_id(), false );
		if ( $authors ) {
			echo '<span class="book-author">' . $authors . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	public static function single_meta() {
		global $product;
		echo self::urdu_title_html( $product->get_id(), 'p' ); // phpcs:ignore WordPress.Security.EscapeOutput
		$authors = self::authors_html( $product->get_id() );
		if ( $authors ) {
			/* translators: %s: author name(s) */
			printf( '<p class="book-author">' . esc_html__( 'by %s', 'kitab-book-details' ) . '</p>', $authors ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	public static function product_tab( $tabs ) {
		global $product;
		if ( ! $product ) {
			return $tabs;
		}
		$tabs['book_details'] = array(
			'title'    => __( 'Book details', 'kitab-book-details' ),
			'priority' => 15,
			'callback' => array( __CLASS__, 'render_product_tab' ),
		);
		return $tabs;
	}

	public static function render_product_tab() {
		global $product;
		$id   = $product->get_id();
		$rows = array();

		$authors = self::authors_html( $id );
		if ( $authors ) {
			$rows[ __( 'Author', 'kitab-book-details' ) ] = $authors;
		}
		foreach ( self::fields() as $key => $label ) {
			if ( '_kitab_title_urdu' === $key ) {
				continue;
			}
			$value = get_post_meta( $id, $key, true );
			if ( '' !== $value ) {
				$rows[ $label ] = esc_html( $value );
			}
		}
		if ( $product->get_sku() ) {
			$rows[ __( 'SKU', 'kitab-book-details' ) ] = esc_html( $product->get_sku() );
		}

		if ( ! $rows ) {
			return;
		}
		echo '<h2>' . esc_html__( 'Book details', 'kitab-book-details' ) . '</h2>';
		echo '<table class="woocommerce-product-attributes shop_attributes book-details-table">';
		foreach ( $rows as $label => $value ) {
			echo '<tr><th>' . esc_html( $label ) . '</th><td>' . $value . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</table>';
	}

	/**
	 * Let the storefront search also match author names, ISBNs and Urdu titles.
	 */
	public static function extend_search( $search, $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() || '' === $search ) {
			return $search;
		}
		$s = trim( (string) $query->get( 's' ) );
		if ( '' === $s ) {
			return $search;
		}

		global $wpdb;
		$like = '%' . $wpdb->esc_like( $s ) . '%';

		$meta_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_kitab_isbn','_kitab_title_urdu') AND meta_value LIKE %s",
				$like
			)
		);
		$term_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT tr.object_id FROM {$wpdb->term_relationships} tr
				 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
				 WHERE tt.taxonomy = %s AND t.name LIKE %s",
				self::TAXONOMY,
				$like
			)
		);

		$ids = array_unique( array_map( 'absint', array_merge( $meta_ids, $term_ids ) ) );
		if ( ! $ids ) {
			return $search;
		}

		$inner = preg_replace( '/^\s*AND\s*/', '', $search, 1 );
		return " AND ( {$wpdb->posts}.ID IN (" . implode( ',', $ids ) . ") OR ( {$inner} ) ) ";
	}

	public static function structured_data( $markup, $product ) {
		$isbn = $product->get_meta( '_kitab_isbn' );
		$terms = get_the_terms( $product->get_id(), self::TAXONOMY );
		if ( $isbn || ( $terms && ! is_wp_error( $terms ) ) ) {
			$markup['@type'] = array( 'Product', 'Book' );
			if ( $isbn ) {
				$markup['isbn'] = $isbn;
			}
			if ( $terms && ! is_wp_error( $terms ) ) {
				$markup['author'] = array_map(
					static function ( $t ) {
						return array( '@type' => 'Person', 'name' => $t->name );
					},
					$terms
				);
			}
			$lang = $product->get_meta( '_kitab_language' );
			if ( $lang ) {
				$markup['inLanguage'] = $lang;
			}
		}
		return $markup;
	}
}

Kitab_Book_Details::init();

register_activation_hook(
	__FILE__,
	static function () {
		Kitab_Book_Details::register_taxonomy();
		flush_rewrite_rules();
	}
);
