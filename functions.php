<?php
// Add custom Theme Functions here





add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );
// add_action('wp_print_styles', 'theme_name_scripts'); // можно использовать этот хук он более поздний
function theme_name_scripts() {
	wp_enqueue_style('gen-styles', get_stylesheet_directory_uri() . '/dist/main.min.css');
	
}

add_shortcode( 'file_links', 'file_links' );
function file_links() {?>
	<?php if(have_rows('files')):?>

				<ul class="file_list">
					<?php while(have_rows('files')): the_row(); ?>
					<li><a href="<?php the_sub_field('file') ?>" download class="button download_file_button"><?php the_sub_field('file_label') ?></a></li>
					<?php endwhile; ?>
				</ul>
	<?php else: ?>
		<p>Дополнительных файлов нет.</p>
	<?php endif; ?>
	<?php
}
add_shortcode( 'stock', 'stock' );
function stock() {?>
		<div class="stock in-stock"><?php the_field('stock-label', 'option') ?></div>
	<?php
}


if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
}


add_shortcode( 'product-power', 'productPowerIsSimilar' );

	function productPowerIsSimilar() {
		ob_start(); 
         ?>
<div style="color: red;">
	
<?php
		global $post;
		 
// 		$brand_terms = get_the_terms($post->ID, 'pa_moshhnost');
// 		$brand_string = ''; // Reset string
// 		foreach ($brand_terms as $term) :
// 			$brand_string .= $term->slug . ' ';
// 			echo $brand_string;
// 		endforeach;
		global $product;
		$productPower = array_shift( wc_get_product_terms( $product->id, 'pa_moshhnost', array( 'fields' => 'names' ) ) );
		
		$lowerProductPower = round($productPower * 1.1);
		$higherProductPower = round($productPower * 0.9);
		
// 		echo $productPower . '</br>'; 
// 		echo $lowerProductPower . '</br>';
// 		echo $higherProductPower . '</br>';
		
		//var_dump(range($lowerProductPower, $higherProductPower));
		// The query
		//echo get_the_ID();
	$products = new WP_Query( array(
	   'post_type'      => array('product'),
	   'post_status'    => 'publish',
	   'posts_per_page' => 3,
	   'post__not_in' => array(get_the_ID()),
	   'tax_query'      => array( array(
			'taxonomy'        => 'pa_moshhnost',
			'field'           => 'slug',
			'terms'           =>  range($lowerProductPower, $higherProductPower),
			'operator'        => 'IN',
		) )
	) );

	// The Loop
	if ( $products->have_posts() ): while ( $products->have_posts() ):
		$products->the_post();
// 		$product_ids[] = $products->post->ID;
// 		echo $products->post->ID;
		//echo get_the_ID() . '</br></br>';
		$productPowerIsSimilar = array();
		array_push($productPowerIsSimilar,get_the_ID());
		//var_dump($productPowerIsSimilar);
		
		foreach($productPowerIsSimilar as $productId) {
			$productPowerIsSimilarList .= $productId . ','; 
		}
		
		//echo $productPowerIsSimilarList;
		
	endwhile;
		wp_reset_postdata();
	endif;

	// TEST: Output the Products IDs
	//print_r($productPowerIsSimilar);
	if ($productPowerIsSimilarList) {?>
		<div class="product-section">
			<h3 class="product-section-title container-width product-section-title-related pt-half pb-half uppercase">Похожие по мощности товары</h3> 
			<?php echo do_shortcode('[products ids="' . $productPowerIsSimilarList . '" ]'); ?>
		</div> 
		<?php
	}
	
?></div><?php
	 return ob_get_clean();
}


/**
 * Remove related products output
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );


/**
 * Change price when product is saved
 */

//add_action('acf/save_post', 'my_acf_save_post', 0);
function my_acf_save_post( $post_id ) {

    // bail early if no ACF data
    if( empty($_POST['acf']) ) {
        return;
    }

    // An array of fields values
    $fields = $_POST['acf'];

	$rateUSD =  get_field("kurs_dolara","option");
	$rateEUR =  get_field("kurs_evro","option");

	$currency = get_field('currencyRadio');
	$priceEUR = get_field('priceEUR', get_the_ID());
	$priceUSD = get_field('priceUSD', get_the_ID());

	if ($currency == 'USD' && isset($fields['field_60c731fdcc1e5'])) {
		$priceUAH = get_field("kurs_dolara","option") * $priceUSD; 
	
		// Push to a specific woocommerce meta data:
		update_post_meta( get_the_ID(), '_regular_price', sanitize_text_field( $priceUAH ) );
		update_post_meta(get_the_ID(), '_price', sanitize_text_field($product_rate));
	}

	elseif ($currency == 'EUR' && isset($fields['field_60c74a75dd912'])) {
		$priceUAH = $rateEUR * $priceEUR;
		
		// Push to a specific woocommerce meta data:
		update_post_meta( get_the_ID(), '_regular_price', sanitize_text_field( $priceUAH ) );
		update_post_meta(get_the_ID(), '_price', sanitize_text_field($product_rate));

	}

	else {
		$priceUAH = get_field('priceUAH',$post->ID);

		// Push to a specific woocommerce meta data:
		update_post_meta( get_the_ID(), '_regular_price', sanitize_text_field( $priceUAH ) );
		update_post_meta(get_the_ID(), '_price', sanitize_text_field($product_rate));
	}
}


add_filter( 'woocommerce_get_price_html', 'bbloomer_alter_price_display', 9999, 2 );
 
function bbloomer_alter_price_display( $price_html, $product ) {
	
    $rateUSD =  get_field("kurs_dolara","option");
	$rateEUR =  get_field("kurs_evro","option");

	$currency = get_field('currencyRadio', $post->ID);
	$priceEUR = get_field('priceEUR', get_the_ID());
	$priceUSD = get_field('priceUSD', get_the_ID());
	
    if ($currency == 'USD') {
		$price_html = get_field("kurs_dolara","option") * $priceUSD; 
	}

	elseif ($currency == 'EUR' && isset($fields['field_60c74a75dd912'])) {
		$price_html = $rateEUR * $priceEUR;
	}

	else {
		$price_html = get_field('priceUAH',$post->ID);
	}
    
    
    return $price_html . " грн";
 
}


add_action( 'woocommerce_before_calculate_totals', 'bbloomer_alter_price_cart', 9999 );
 
function bbloomer_alter_price_cart( $cart ) {
 
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
 
    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) return;
	
	$rateUSD =  get_field("kurs_dolara","option");
	$rateEUR =  get_field("kurs_evro","option");

	
	
	
 
    // LOOP THROUGH CART ITEMS & APPLY 20% DISCOUNT
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		
        $product = $cart_item['data'];
		
		$currency = get_field('currencyRadio', $product->id);
		$priceEUR = get_field('priceEUR', $product->id);
		$priceUSD = get_field('priceUSD', $product->id);
		
        $price = $product->get_price();
        
		    if ($currency == 'USD') {
				$price_html = get_field("kurs_dolara","option") * $priceUSD; 
			}

			elseif ($currency == 'EUR' && isset($fields['field_60c74a75dd912'])) {
				$price_html = $rateEUR * $priceEUR;
			}

			else {
				$price_html = get_field('priceUAH',$post->ID);
			}
		
		$cart_item['data']->set_price( $price_html );
    }
 
}
function mytheme_customize_register( $wp_customize )
{
   $wp_customize->remove_section('custom_css');
}
add_action( 'customize_register', 'mytheme_customize_register' );

/**
 * Replaced single excerpt under meta 
 */

remove_action( 'woocommerce_single_product_summary','woocommerce_template_single_excerpt',20);
add_action( 'woocommerce_single_product_summary','woocommerce_template_single_excerpt',45);
