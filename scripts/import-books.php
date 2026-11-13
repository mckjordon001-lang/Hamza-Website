<?php
/**
 * Imports categories and books from sample-data/books.json.
 *
 * Usage: wp eval-file scripts/import-books.php [path/to/books.json]
 * Safe to run more than once: books are matched by SKU and skipped if present.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WooCommerce' ) ) {
	fwrite( STDERR, "Run with WP-CLI on a site where WooCommerce is active.\n" );
	exit( 1 );
}

$file = isset( $args[0] ) ? $args[0] : dirname( __DIR__ ) . '/sample-data/books.json';
if ( ! is_readable( $file ) ) {
	WP_CLI::error( "Cannot read $file" );
}
$data = json_decode( file_get_contents( $file ), true );
if ( ! is_array( $data ) ) {
	WP_CLI::error( 'Invalid JSON in ' . $file );
}

// Categories.
$cat_ids = array();
foreach ( $data['categories'] as $i => $cat ) {
	$term = get_term_by( 'slug', $cat['slug'], 'product_cat' );
	if ( ! $term ) {
		$res = wp_insert_term( $cat['name'], 'product_cat', array( 'slug' => $cat['slug'], 'description' => $cat['description'] ) );
		if ( is_wp_error( $res ) ) {
			WP_CLI::warning( $res->get_error_message() );
			continue;
		}
		$term_id = $res['term_id'];
		WP_CLI::log( "Category: {$cat['name']}" );
	} else {
		$term_id = $term->term_id;
	}
	update_term_meta( $term_id, 'order', $i );
	$cat_ids[ $cat['slug'] ] = (int) $term_id;
}

// Books.
$created = 0;
foreach ( $data['books'] as $book ) {
	if ( wc_get_product_id_by_sku( $book['sku'] ) ) {
		WP_CLI::log( "Skip (exists): {$book['title']}" );
		continue;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $book['title'] );
	$product->set_status( 'publish' );
	$product->set_sku( $book['sku'] );
	$product->set_regular_price( (string) $book['price'] );
	if ( ! empty( $book['sale'] ) ) {
		$product->set_sale_price( (string) $book['sale'] );
	}
	$product->set_short_description( $book['short'] );
	$product->set_description( $book['description'] );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( isset( $book['stock'] ) ? (int) $book['stock'] : 20 );
	$product->set_featured( ! empty( $book['featured'] ) );
	$product->set_total_sales( isset( $book['sales'] ) ? (int) $book['sales'] : 0 );
	if ( isset( $cat_ids[ $book['category'] ] ) ) {
		$product->set_category_ids( array( $cat_ids[ $book['category'] ] ) );
	}
	$product->update_meta_data( '_kitab_language', $book['language'] );
	$product->update_meta_data( '_kitab_format', $book['format'] );
	if ( ! empty( $book['urdu'] ) ) {
		$product->update_meta_data( '_kitab_title_urdu', $book['urdu'] );
	}
	$id = $product->save();

	if ( taxonomy_exists( 'book_author' ) ) {
		wp_set_object_terms( $id, $book['authors'], 'book_author' );
	}
	$created++;
	WP_CLI::log( "Book: {$book['title']}" );
}

WP_CLI::success( "Imported $created books." );
