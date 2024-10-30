<?php get_header(); ?>
<?php
	$grid_post_type = isset($_REQUEST['post_type']) ? sanitize_text_field( $_REQUEST['post_type'] ) : 'post';
	
	?>
	<div id="grid-preview-container">
		<div id="grid-preview-device" class="em-pm_wrapper" style="padding-top: 20px">
			<?php do_shortcode('[maxgrid id="68vyKmWJyVA" post_type="'.$grid_post_type.'" exclude="" dflt_mode="grid" masonry="on" full_content="off" ribbon="off" filter="on" pagination="load_more_button" grid_ppp="8"]'); ?>		 
		</div>
	</div>	
	<style type="text/css">
		body, html {
			overflow:hidden;
		}
		#ajax-loading-screen {
			background-color: #f2f2f2!important;
		}
		#ajax-loading-screen .loading-icon {
			display: none!important;
		}
		#wpadminbar {
			display: none!important;
		}
		#grid-preview-container {
			position: fixed;
			display: block;
			background: #efefef;
			width: 100%;
			height: 100%;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			box-sizing: border-box;
		}
		#grid-preview-device {
			display: block;
			background: #fff;
			padding: 0 20px;
			width: 100%;
			height: 100%;
			margin: 0 auto;
			overflow-y: auto;
			box-sizing: border-box;
			-webkit-transition: all 250ms ease-in-out;
			  -ms-transition: all 250ms ease-in-out;
			  -moz-transition: all 250ms ease-in-out;
			  transition: all 250ms ease-in-out;
		}
		header, #header-space, #header-outer, #footer-outer, #header, .entry-content, footer, .site-footer, .site-info, body:before, body:after  {
			display: none!important;
		}

		body, #content, .site-content, .site {
			width: 100%;
			margin: 0px;
			border: 0px;
			padding: 0px;
			max-width: 100%;
		}
	</style>

<?php get_footer();?>