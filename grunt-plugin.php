<?php
/**
 * Plugin Name: Grunt Sitemap Generator
 * Plugin URI: http://www.github.com/lgladdy
 * Description: Generate a JSON list of every page on a site so it can be used with grunt and uncss. Create a folder in /wp-content called mu-plugins, and drop this code into that folder, as grunt-sitemap.php
 * Author: Liam Gladdy /\ Will Rees
 * Author URI: http://gladdy.co.uk
 * Version: 1.1
 */
 
add_action('template_redirect','show_sitemap');

function show_sitemap() {
  if (isset($_GET['show_sitemap'])) {
    
    //set up an array for all the URLs
    
    	$urls = array();
    	
	//Homepage
	
		$urls[] = get_site_url();
	
	//get all the pages, posts (including CPTs)
	
		$the_query = new WP_Query(array('post_type' => 'any', 'posts_per_page' => '-1', 'post_status' => 'publish'));
    
		while ($the_query->have_posts()) {
		 
		  $the_query->the_post();

		  	$urls[] = get_permalink();
	  
		}
		
	//Authors

		 $authors = get_users();
		 
		 foreach ($authors as $author) {
		
			 $urls[] = get_author_posts_url( $author->ID );
			 
		 }
		  
	 //Every term imaginable, even the empty ones (categories, custom taxonomies, tags, etc.)
	
		$args = array(
  			'public'   => true,
  		);
  			
  			$taxonomies=get_taxonomies($args,'names'); 

				$args = array( 'hide_empty=0' );

					$terms = get_terms($taxonomies, $args);

						foreach ($terms as $term) {
			 
							$urls[] = get_term_link( $term );    	
						}
				    
	//Getting a list of Archive URLs seems like it should be easier...probably missing something on the Codex, but this works.			    	    
				    
		$args = array(
			'type'            => 'monthly',
			'format'          => 'custom', 
			'before'          => '',
			'after'           => '',
			'show_post_count' => false,
			'echo'            => 0
		); 
					    
			$archive_links_raw = wp_get_archives($args);
		
			$archive_links_pattern = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";	
				
				preg_match_all($archive_links_pattern, $archive_links_raw, $cleaned_archive_links);
					
					$cleaned_archive_links = $cleaned_archive_links[1];
					
						foreach ($cleaned_archive_links as $cleaned_archive_link) {
						
							$urls[] = $cleaned_archive_link;
						
						}
						
	//Add in a search result page for '.' 
	
		$main_url = get_site_url();
		
			$urls[] = $main_url . '/?s=.';
			
	//Force a search with no results
	
		$urls[] = $main_url . '/?s=asdfasdfasdfasdf';
	
	//Force a 404 page
	
		$urls[] = $main_url . '/asdfasdfasdfasdf';

//Return all of the urls captured above and encode to json for UnCSS

	die(json_encode($urls));
  
  }

}
?>