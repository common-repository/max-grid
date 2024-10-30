<?php
/**
 * Description, Comments and Reviews Tabs.
 *
 * This class will show 'description' and 'reviews / comments' tabs.
 * 				   Show 'reviews' tab if 'product' or 'download' post type.
 * 				   Show 'comments' tab if not custom post type.
 *
 * @version 1.0.0
 */

namespace MaxGrid;
use \MaxGrid\g_recaptcha;

defined( 'ABSPATH' ) || exit;

/**
 * @class Tabs.
 */
class Tabs {
	
	public $template;
	
	/**
     * Default values for query vars.
     *
     * @since 1.0.0
     * @var array
	 */
	public $data = array();
	public $post_type;
		
	/**
     * Constructor.
	 *
	 * @param $data array
	 */
	public function __construct($data=array()) {
		$this->template = maxgrid()->template;
		$this->data = $data;
		$this->post_type = get_post_type( $this->data['post_id'] );
	}
	
	/**
	 * Customize the text prior to the comment form fields. Leave empty to disable it.
	 *
	 * @return string
	 */
	private function comment_notes(){
		if ( isset($this->data['comment_notes']) && $this->data['comment_notes'] != '' ) {
	 		return '<p class="comment-notes">
					' . $this->data['comment_notes'] . '
					<span class="required">
					  *
					</span>
				  </p>';
		}
	}

	/**
     * Add comment input fields.
     *
	 * @return string
	 */
	private function input($name, $label=''){
		$require_name_email = get_site_option( 'require_name_email' );
		if ( $require_name_email == true ) {
	 		return '<span class="comment-form-' . $name . '">
						<label for="' . $name . '">
						  ' . $label . ' 
						  <span class="required">
							*
						  </span>
						</label>
						<input id="' . $name . '" name="' . $name . '" type="text" value="" size="30" aria-required="true">
						<div class="form_validate-alert"></div>
					</span>';
		}
	}
	
	/**
     * Add comment reply link.
     *
	 * @return string
	 */
	private function comment_reply($comment_id, $comment_author){
		if ( isset($this->data['form_type']) && $this->data['form_type'] == 'comments' ) {
	 		return '<div class="reply">
						<a rel="nofollow" href="#" class="comment-reply-link" data-comment-id="'.$comment_id.'" aria-label="'. __( 'To', 'max-grid' ) . ' '.$comment_author.'" onclick="return false;">'. __( 'Reply', 'max-grid' ) . '</a>
					</div>';
		}
	}
	
	/**
     * Add comment form.
     *
	 * @return string
	 */
    private function Form(){		
		if ( $this->post_type == 'post' ) {
			$form_title = __( 'Leave a Reply', 'max-grid' );
			$form_type = 'comment';
			$rating_field = '';
		} else {
			$form_title = __( 'Leave a Review', 'max-grid' );
			$form_type = 'review';
			$rating_field = '<input type="hidden" id="rating" name="rating" value="0">';
		}		
		$html = '<div class="e_comment_form-title">' . $form_title . '</div><span id="maxgrid-replay-to" style="margin-left: 10px;"></span>';
		$html .= '<form action="'.get_site_url().'/wp-comments-post.php" method="post" id="' . $this->data['form_id'] . '" class="comment-form" novalidate="">';
		
		$html .= self::comment_notes();
		
		if ( !is_user_logged_in() ) {		
			$html .= self::input('author', 'Name')
				    .self::input('email', 'Email');
		}
		
		$g_recaptcha = '';
		if ( is_maxgrid_premium_activated() ) {
			$captcha = new g_recaptcha();
			$g_recaptcha = $captcha->g_recaptcha();
		}
		
		$html .= '<span class="comment-form-comment">
						<textarea id="comment" data-type="' . $form_type . '" name="comment" cols="45" rows="8" aria-required="true"></textarea>
						<div class="form_validate-alert"></div>
					  </span>
					  '.$g_recaptcha.'
					  <div class="form-submit e_pg_lightbox">
					  	<input type="button" id="ajax_submit" value="' . __( 'Post Comment', 'max-grid' ) . '" data-post-id="'.$this->data['post_id'].'">
						<input type="hidden" name="comment_post_ID" value="'.$this->data['post_id'].'" id="comment_post_ID">
						<input type="hidden" name="comment_parent" id="comment_parent" value="0">
						' . $rating_field . '
						<div class="comment_success-msg"></div>
					  </div>
					  
					</form>';
		
		return $html;
    }
	
	/**
     * Add edit comment link.
     *
	 * @return string
	 */
	public function editComment($comment_id) {		
		// gets the current user
		$user = wp_get_current_user();

		// allowed roles
		$allowed_roles = array('editor', 'administrator', 'author');

		// Show edit comment link if user have permission.
		$html = '';
		if( array_intersect($allowed_roles, $user->roles ) ) {
		   $html = '<a class="comment-edit-link" href="'.get_home_url().'/wp-admin/comment.php?action=editcomment&amp;c='.$comment_id.'" target="_blank">('. __( 'Edit', 'max-grid' ) . ')</a>';
		}
		return $html;
	}
	
	/**
     * Get a list of child comments matching the comment_parent id.
	 *
	 * 1 - List of child comments
	 * 2 - Array of child comments ids if '$parent_id 'is not empty
	 * 3 - Number of found comments if '$count' argument is true.
     *
     * @return int|array
     */
	public function getCommentChild($post_id='', $parent_id='', $count=false) {
		$args = array(
			'post_id' => $post_id,
			'status' => 'approve',
			'order' => 'ASC',
		);
		
		// The Query
		$comments = get_comments($args);		
		$html = '';
		$comments_count = 0;
		// Comment Loop
		foreach($comments as $comment) {
			
			// Ignor comment if his comment_parent id not equal to $parent_id
			if ( $comment->comment_parent != $parent_id ) {
				continue;
			}

			// Return only total chold comment count
			if ( $count ) {
				$comments_count++;
				continue;
			}
			
			$args = array(
				'time_ago' 	=> true,
				'date_time'	=> $comment->comment_date,
			);
			
			// comment body
			$html .= '<div class="comment-body children">';
			$html .= '<div class="comment-author vcard">
								<img alt="" src="'.get_avatar_url($comment->comment_author_email).'" srcset="http://2.gravatar.com/avatar/ba74e25c8ae674ef528dca94c879799b?s=120&amp;d=mm&amp;r=g 2x" class="avatar avatar-60 photo" width="60" height="60">
								<cite class="fn">'.$comment->comment_author.'</cite>
							</div>

							<div class="comment-meta commentmetadata">
								<span class="comment-time-ago">' . $this->template->dateTime($args) . '</span>
								'  . $this->editComment($comment->comment_ID) . '
							</div>
							' . $get_rating . '
							<p>' . $comment->comment_content . '</p>
							
							' . self::comment_reply($comment->comment_ID, $comment->comment_author);
			$html .= '</div>'; 
		}
		if ( $count ) {
			return $comments_count;
		}
		return $html;
	}
	
	/**
     * Get a list of child comments ids matching the query vars.
     *
     * @return array List of child comments ids.
     */
	public function getCommentChild_ID() {
		
		$args = array(
			'post_id' => $this->data['post_id'],
			'status' => 'approve',
		);
		
		// The Query
		$comments = get_comments($args);
		$ids = array();
		
		// Comment loop
		foreach($comments as $comment) {
			
			// Ignor comment if his comment_parent id equal to '0'
			if ( $comment->comment_parent == '0' ) { 
				continue;
			}
			
			$ids[] = $comment->comment_ID; 
		}
		return $ids;
	}
	
	/**
     * Get last comment id.
     *
     * @return string last comment id.
     */
	public function getLast($args,$reverse=true) {		
		$args['status'] = 'all';
		$args['number'] = '';
		$args['comment__not_in'] = '';
		
		$comments = get_comments($args);
		if ($reverse) {
			return end($comments)->comment_ID;			
		}
		return current($comments)->comment_ID;
	}
		
	/**
     * Get a list of comments matching the query vars.
     *
     * @return array List of comments.
     */	
	public function CommentsList( $get_last=false, $reverse=true ) {		
		$args = array(
			'status'		  => 'all',
			'order'			  => $this->data['order'],
			'offset'    	  => $this->data['offset'],
			'number' 		  => $this->data['number'],
			'post_id' 		  => $this->data['post_id'],
			'comment__not_in' => $this->getCommentChild_ID(),
		);
		
		$page = $this->data['offset']/$this->data['number']+1;
		
		// The Query
		$comments = get_comments($args);
			
		// Comment loop
		$html = '';
		$x = 0;
		
		if( isset($this->data['next_list_token']) ) {
			$x = $this->data['next_list_token'];
		}
		$total_child = 0;
		
		foreach($comments as $comment) {
			
			// Ignor child comments
			if ( $comment->comment_parent != '0' ) {				
				continue;
			}
			
			if ( $get_last && $comment->comment_ID != $this->getLast($args, $reverse) ) {
				continue;
			}
			
			// Show unapproved comment only to his author
			$ip =  $_SERVER['REMOTE_ADDR'];
			if ( !$comment->comment_approved && $comment->comment_author_IP != $ip ) {				
				continue;
			}
			
			$the_last = $get_last ? ' the_last' : '';
			$unapproved = !$comment->comment_approved ? ' unapproved' : '';
			
			$child_count = $this->getCommentChild($this->data['post_id'], $comment->comment_ID, true);
			
			$get_rating = '';
			
			if ( is_maxgrid_download_activated() || is_maxgrid_woo_activated() ) {
				// Get Rating class
				$rating = maxgrid()->rating;
				
				// Reviews rating system
				$rating_comment_meta = get_comment_meta( $comment->comment_ID, 'rating', true );
				$rating->set_variable($comment->comment_ID, $rating_comment_meta, 'get');
				
				if ( isset($this->data['form_type']) && $this->data['form_type'] == 'reviews' || $get_last ) {
					$get_rating = $rating->get_rating();
				}
			}
			
			// get date time arguments
			$args = array(
				'time_ago' 	=> true,
				'date_time'	=> $comment->comment_date,
			);
						
			// Comment body
			$html .= '<div class="comment-body' . $unapproved . $the_last . '" data-id="' . $comment->comment_ID . '">';
			$html .= '<div class="unapproved-comment">
					  	  <div class="maxgrid_alert">' . __( 'Your comment is awaiting moderation.', 'max-grid' ) . '</div>
					 </div>';
			$html .= '<div class="comment-author vcard">
								<img alt="" src="'.get_avatar_url($comment->comment_author_email).'" srcset="http://2.gravatar.com/avatar/ba74e25c8ae674ef528dca94c879799b?s=120&amp;d=mm&amp;r=g 2x" class="avatar avatar-60 photo" width="60" height="60">
								<cite class="fn">'.$comment->comment_author.'</cite>
							</div>
							<div class="comment-meta commentmetadata">
								<span class="comment-time-ago">' . $this->template->dateTime($args) . '</span>
								'  . $this->editComment($comment->comment_ID) . '
							</div>
							' . $get_rating . '
							<p>' . $comment->comment_content . '</p>
							
							' . self::comment_reply($comment->comment_ID, $comment->comment_author);
			
			if ( !empty($child_count) ) {				
				if ( $child_count == 1 ) {
					$text = __( 'View reply', 'max-grid' );
				} else if ( $child_count > 1 ) {					
					$text = sprintf( __( 'View all %s replies', 'max-grid' ) , $child_count ); 
				}				
				$html .= '<div class="view-replay-btn" data-post-id="' . $this->data['post_id'] . '" data-parent-id="' . $comment->comment_ID . '" data-count="' . $child_count . '">' . $text . '</div><div class="e_comments_replies_response id_' . $comment->comment_ID . '"></div>';
			}
			
			$html .= '</div>';
			$total_child += $child_count;
			$x++; 
		}
		if( $x == 0 && $this->data['offset'] == 0 && !$get_last ) {
			$html .= '<div class="no_comment-found">'. __( 'There are no comments yet. Be the first to comment.', 'max-grid' ) . '</div>';
		}
		// Show 'Load More' button
		$comments_count = wp_count_comments($this->data['post_id']);
		$next_list_token = $x+intval($total_child)+1;

		if( $next_list_token < $comments_count->total_comments && !$get_last ) {
			$html .= '<div class="load-more_container comments"><span class="load-more_comments post" data-post-id="'.$this->data['post_id'].'" data-comments-page="'.$page.'" data-getlast="'.$this->getLast($args).'" data-next-list-token="'.$next_list_token.'">' . __( 'Load More', 'max-grid' ) . '</span></div>';
		}
		
		return $html;
	}

	/**
     * Get a list of tabs link matching the query vars.
     *
     * @return array List of tabs link.
     */
	private function TabLinks(){		
		$html = '<ul class="tabs">';
		$comments_count = wp_count_comments($this->data['post_id']);
		$pg_theme = 'pg_light_theme';
		
		$tabs = $this->data['tabs'];
				
		foreach( $tabs as $key => $value ) {
			$current = '';
			$total_comments = '';
			if ( $tabs[$key]['id'] == $this->data['default_tab'] ) {
				$current = ' current';
			}
			if ( $tabs[$key]['id'] == 'tab-reviews' && $comments_count->total_comments > 0 ) {
				$total_comments = ' ('.$comments_count->total_comments.')';
			}
			$tabename = $tabs[$key]['name'];			 
			if(maxgrid_is_mobile()){
				$tabename = $tabs[$key]['icon'];
			}
			$html .= '<li class="tab-link ' . $pg_theme . $current . '" data-tab="' . $tabs[$key]['id'] . '">' . $tabename . $total_comments . '</li>';
		}
		$html .= '<li class="tab-link '.$pg_theme.' tab-playlist" data-tab="tab-playlist"><i class="fa fa-th-list"></i></i></li>';
		$html .= '</ul>';
		return $html;
	}
	
	/**
     * Get a list of tabs content matching the query vars.
     *
     * @return array List of tabs content.
     */	
	private function TabContents(){
		
		$Title = '';
		
		if ( $this->post_type == MAXGRID_POST ) {
			$Title = 'Download';
		} else if ( $this->post_type == 'product' ) {
			$Title = 'Product';
		}
		
		$tabs = $this->data['tabs'];
		
		$html = '';
		foreach($tabs as $key => $value ) {

			if ( $this->data['comments_only'] == true && $tabs[$key]['id'] == 'tab-description' ) {
				continue;	
			}
			
			$current = '';
			if ( $tabs[$key]['id'] == $this->data['default_tab'] ) {
				$current = ' current';
			}
			
			if ( $tabs[$key]['id'] == 'tab-reviews' ) {
				$html .= self::reviewsTab($current);
			} else {
				$html .= '<div id="' . $tabs[$key]['id'] . '" class="tab-content' . $current . '">';
				$html .= '<h2>' . $Title . '</h2>';
				$html .= '<p>' . $tabs[$key]['the_content'] . '</p>';
				$html .= '</div>';
			}
		}
		// Playlist Tab Content - if isMobile mode		
		$html .= '<div id="tab-playlist" class="tab-content">
					<div class="playlist-search-bar maxgrid-parent ismobile">
						<div class="box">
						  <div class="container-3">
							  <span class="icon"><i class="fa fa-search"></i></span>
							  <input type="search" id="playlist-search" class="tab-playlist-search" data-trigger="isMobile_" onkeyup="postPlaylistFilter(this)" data-is-mobile="on" placeholder="' . ucfirst( __( 'search', 'max-grid' )) . '..." />
							  <span class="clear-woord" data-is-mobile="on">&times;</span>
						  </div>
						</div>
					</div>
					<div class="playlist-tab-content">
						<p>no content!</p>
					</div>
				 </div>';
		return $html;
	}

	/**
     * Construct reviews tab content.
     *
	 * @return string
	 */
	private function reviewsTab($current='') {		
		$comments_only =  ''; 
		if ( isset($this->data['comments_only']) && $this->data['comments_only'] == true ) {
			 $comments_only =  ' maxgrid comments_only'; 
		}
		
		$get_rating = '';
		if ( is_maxgrid_download_activated() || is_maxgrid_woo_activated() ) {
			// Call Rating class
			$rating = maxgrid()->rating;
			$rating->set_variable($this->data['post_id']);
			
			if ( isset($this->data['form_type']) && $this->data['form_type'] == 'reviews' ) {
				$get_rating = $rating->get_rating();
			}
		}
		
		// Construct reviews tab content
		$html = '<div id="tab-reviews" class="tab-content maxgrid-parent maxgrid-form' . $current . $comments_only . '">
					<div id="reviews" class="epg_builder-Reviews">
						<div id="comments">
							<div class="comments-container">'. $this->CommentsList() .'</div>
						</div>
						<div id="review_form_wrapper" class="maxgrid-ratings">
							<div class="form_validate-alert"></div>
							' . $get_rating . '
						</div>
						<div id="review_form_wrapper">
							<div id="review_form">			
								' . self::Form() . '
							</div>
						</div>
					</div>
				</div>';
		
		return $html;
	}
	
	/**
     * Construct Tab body.
     *
	 * @return string
	 */
	public function Tabs() {
		extract(maxgrid_get_options());
		
		$total_comments = wp_count_comments($this->data['post_id']);
		$review_count = $total_comments->total_comments;
				
		if ( $this->data['comments_only'] == true ) {
			$get_comments_count = wp_count_comments($this->data['post_id']);
			$count = $get_comments_count->total_comments;
			if ( $count > 1 ){
				$comments_count = $count.' '.__('Comments', 'max-grid');
			} else if ( $count == 1 ){
				$comments_count = __('One Comment', 'max-grid');
			} else if ( $count == 0 ){
				$comments_count = '';// __('No Comment', 'max-grid')
			}
			$average = '';
			if ($this->post_type == 'product') {
				if ( is_maxgrid_download_activated() || is_maxgrid_woo_activated() ) {
					$rating = maxgrid()->rating;
					$data = array(
						'post_id' => $this->data['post_id'],
						'stars_size' => 'large',
					);
					$average = $rating->get_average($data);
				}
				$join_discussion_text = __( 'Product reviews', 'max-grid' );
				$comments_count = '';
			} else {
				$join_discussion_text = $comment_form_title;
			}
			
			$html = '<h3 id="join_discussion"><span>' . $join_discussion_text . '</span>
					<span class="comments_count">' . $comments_count . '</span></h3>'.$average;
			$html .= self::TabContents();			
		} else {
			$html = '<div class="reviews_tabs_container">';
			$html .= self::TabLinks();
			$html .= self::TabContents();
			$html .= '</div>';	
		}		
		return $html;
	}
}