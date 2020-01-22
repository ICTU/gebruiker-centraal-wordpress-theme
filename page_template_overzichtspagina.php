<?php

// * Gebruiker Centraal - page_template_overzichtspagina.php
// * ----------------------------------------------------------------------------------
// * Page template to display a grid with various types of content
// * ----------------------------------------------------------------------------------
// * ----------------------------------------------------------------------------------
// * @package gebruiker-centraal
// * @author  Paul van Buuren
// * @license GPL-2.0+
// * @version 4.1.3
// * @since   4.1.3
// * @desc.   Moved card backend functions and page_template_overzichtspagina from inlusie plugin to theme.
// * @link    https://github.com/ICTU/gebruiker-centraal-wordpress-theme


//* Template Name: Overzichtspagina voor tips, vaardigheden, methodes


// template voor vaardighedenpagina.

remove_action( 'genesis_loop', 'genesis_do_loop' );
				
add_action( 'genesis_loop', 'gc_page_template_loop', 10 );

add_action('wp_enqueue_scripts', 'ictu_gc_append_header_css_local' );

// tips toevoegen
add_action( 'genesis_loop', 'ictu_gc_frontend_general_get_related_content', 12 );



genesis();




/**
 * Adds extra CSS to header for background images in cards
 *
 * @since 4.1.3
 *
 */
function ictu_gc_append_header_css_local() {

    global $post;

	wp_enqueue_style(
		ID_BLOGBERICHTEN_CSS,
		WBVB_THEMEFOLDER . '/blogberichten.css?v=' . CHILD_THEME_VERSION
	);

    $header_css				= '';
    $acfid					= get_the_id();
    $gerelateerdecontent	= get_field('gerelateerde_content_toevoegen', $acfid);

    $all_or_some = get_field('overzichtspagina_showall_or_select', $acfid );

    if ('showsome' === $all_or_some) {


        $items = get_field('overzichtspagina_kies_items', $post->ID);

        if ($items) {

            foreach ($items as $post):

	            setup_postdata($post);
	
	            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
	
	            if ($image[0]) {
	                $header_css .= "#related_" . $post->ID . " .card__image { ";
	                $header_css .= "background-image: url('" . $image[0] . "'); ";
	                $header_css .= "} ";
	            }

            endforeach;
	        
            wp_reset_query();

        }
    }
    else {

		$doelgroeppagina		= get_field('themesettings_inclusie_doelgroeppagina', 'option');    
		$vaardighedenpagina 	= get_field('themesettings_inclusie_vaardighedenpagina', 'option');    
		$methodepagina 			= get_field('themesettings_inclusie_methodepagina', 'option');    
		$tipspagina 			= get_field('themesettings_inclusie_tipspagina', 'option');    
		

		// by default select vaardigheden
		$select_contenttype = ICTU_GC_CPT_VAARDIGHEDEN;				
		
		if ( is_object( $doelgroeppagina ) && $doelgroeppagina->ID == $currentpageID ) {
			$select_contenttype = ICTU_GC_CPT_DOELGROEP;				
		}
		elseif ( is_object( $tipspagina ) && $tipspagina->ID == $currentpageID ) {
			$select_contenttype = ICTU_GC_CPT_PROCESTIP;				
		}
		elseif ( is_object( $methodepagina ) && $methodepagina->ID == $currentpageID ) {
			$select_contenttype = ICTU_GC_CPT_METHODE;				
		}
		

        $args = array(
          'post_type' => $select_contenttype,
          'posts_per_page' => -1,
          'order' => 'ASC',
          'orderby' => 'post_title',
        );
        
        $items = new WP_query($args);

		if ($items->have_posts()) {

            while ($items->have_posts()) : $items->the_post();

	            setup_postdata($post);
	
	            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
	
	            if ($image[0]) {
	                $header_css .= "#related_" . $post->ID . " .card__image { ";
	                $header_css .= "background-image: url('" . $image[0] . "'); ";
	                $header_css .= "} ";
	            }
            
            endwhile;
		}

    }

    if ($header_css) {
        wp_add_inline_style(ID_BLOGBERICHTEN_CSS, $header_css);
    }

}

/**
 * extra content for page with template = page_template_overzichtspagina
 * either show all content for self determined content type or only the 
 * items selected
 *
 * @return void
 */
function gc_page_template_loop() {

    global $post;
    
    $currentpageID = get_the_id();

        
    $content = $post->post_content;
    $hoofdcontent = apply_filters('the_content', $content);

	$inleiding = get_field('overzichtspagina_inleiding', $currentpageID );

	echo '<article class="page entry">';
	
	echo '<header class="entry-header inleiding wrap"><h1 class="entry-title">' . get_the_title( $currentpageID ) . '</h1>';
	if ( $inleiding ) {
        echo $inleiding;
    }
    echo '</header>';
    if ( $hoofdcontent ) {
		echo '<div class="entry-content wrap">' . $hoofdcontent . '</div>';
    }
    

    $all_or_some = get_field('overzichtspagina_showall_or_select', $post->ID);

    if ('showsome' === $all_or_some) {

        $items = get_field('overzichtspagina_kies_items', $post->ID);

        if ($items) {

			$columncounter = 'grid--col-2';
			$countcount = count( $items );
		
			if ( $countcount < 2  ) {
				$columncounter = 'grid--col-1';
			}
			elseif ( $countcount === 4 ) {
				$columncounter = 'grid--col-2';
			}
			elseif ( $countcount > 2  ) {
				$columncounter = 'grid--col-3';
			}


            echo '<div class="grid ' . $columncounter . '">';

            $postcounter = 0;

            foreach ($items as $post):

                setup_postdata($post);
                $postcounter++;

                if ( ICTU_GC_CPT_DOELGROEP == get_post_type( $post ) ) {
                    $citaat = get_field('facts_citaten', $post->ID);
                    echo ictu_gctheme_card_doelgroep($post, $citaat);
                }
                elseif ( ( GC_BEELDBANK_BRIEF_CPT == get_post_type( $post ) )  || ( GC_BEELDBANK_BEELD_CPT == get_post_type( $post ) ) ) {
                    echo ictu_gctheme_card_featuredimage( $post );
                }
                else {
                    echo ictu_gctheme_card_general( $post );
                }

            endforeach;

            echo '</div>';

            wp_reset_query();

        }
        else {
            echo '<p>' . _x('Geen items geselecteerd voor deze pagina.', "error", "ictu-gc-posttypes-inclusie") . '</p>';
        }
    }
    else {

		$doelgroeppagina		= get_field('themesettings_inclusie_doelgroeppagina', 'option');    
		$vaardighedenpagina 	= get_field('themesettings_inclusie_vaardighedenpagina', 'option');    
		$methodepagina 			= get_field('themesettings_inclusie_methodepagina', 'option');    
		$tipspagina 			= get_field('themesettings_inclusie_tipspagina', 'option');    
		

		// by default select vaardigheden
		$select_contenttype = ICTU_GC_CPT_VAARDIGHEDEN;				
		
		if ( is_object( $doelgroeppagina ) && $doelgroeppagina->ID == $currentpageID ) {
			$select_contenttype = ICTU_GC_CPT_DOELGROEP;				
		}
		elseif ( is_object( $tipspagina ) && $tipspagina->ID == $currentpageID ) {
			$select_contenttype = ICTU_GC_CPT_PROCESTIP;				
		}
		elseif ( is_object( $methodepagina ) && $methodepagina->ID == $currentpageID ) {
			$select_contenttype = ICTU_GC_CPT_METHODE;				
		}
		

        $args = array(
          'post_type' => $select_contenttype,
          'posts_per_page' => -1,
          'order' => 'ASC',
          'orderby' => 'post_title',
        );
        
        $items = new WP_query($args);

        if ($items->have_posts()) {

			$columncounter = 'grid--col-2';
			$countcount = $items->found_posts;
		
			if ( $countcount < 2  ) {
				$columncounter = 'grid--col-1';
			}
			elseif ( $countcount === 4 ) {
				$columncounter = 'grid--col-2';
			}
			elseif ( $countcount > 2  ) {
				$columncounter = 'grid--col-3';
			}

            echo '<div class="grid ' . $columncounter . '">';

            $postcounter = 0;

            while ($items->have_posts()) : $items->the_post();

                $postcounter++;
                if ( ICTU_GC_CPT_DOELGROEP == get_post_type( $post ) ) {
                    $citaat = get_field('facts_citaten', $post->ID);
                    echo ictu_gctheme_card_doelgroep($post, $citaat);
                }
                elseif ( ( GC_BEELDBANK_BRIEF_CPT == get_post_type( $post ) )  || ( GC_BEELDBANK_BEELD_CPT == get_post_type( $post ) ) ) {
                    echo ictu_gctheme_card_featuredimage( $post );
                }
                else {
                    echo ictu_gctheme_card_general( $post );
                }

            endwhile;

            echo '</div>';

            wp_reset_query();

        }
    }

	echo '</article>';

}


