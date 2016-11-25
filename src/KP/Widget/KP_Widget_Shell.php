<?php
    /**
     * A widget shell is used to preserve the naming standard and parent functions
     * defined in KP_Entity as PHP doesn't not allow for multiple inheritance.
     */
    class KP_Widget_Shell extends WP_Widget {
        protected $label, $machine_name;
        
        public function __construct( $label, $machine_name, $description ) {
            $widget_ops = array(
                'classname'     => $machine_name,
                'description'   => $description
            );
            
            /**
             * Get arguments of function, swap the position of the label
             * and machine name then add the widget options to the args array.!pinfo day
             */
            $args = func_get_args();
            $args = $this->swap_indexes( $args, 0, 1 );
            $args[] = $widget_ops;
            
            /** 
             * Call the parent constructor to register the widget with Wordpress
             * 
             * By using 'call_user_func' and 'func_get_args' we can keep using the
             * same naming standards ($label and $machine_name) that have been used.
             */
            call_user_func_array( array( $this, 'parent::__construct' ), $args );
        }

	/**
	 * Handles the front-end display of the widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
	    
		// Outputs Content of the Widget
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$number = $instance['number'];

		echo $before_widget;
		
		if($title) {
			echo $before_title . $title . $after_title;
		} ?>
		<div class="events-sidebar">
			
		<ul>
			<?php
				$args = array(
				'post_type' => 'event',
				'posts_per_page' => $number,
		        'order'   => 'ASC',
		        'orderby' => 'meta_value_num',
		        'meta_key' => 'core_event_date'
		 	);
			
			$latest_events = new WP_Query($args);
			
			if ($latest_events->have_posts()):
				
			global $post;
			 	
			while($latest_events->have_posts()): $latest_events->the_post();
				
			$icare_event_date = rwmb_meta('core_event_date');
				 
			?>
			<li class="event-item">
				<?php if (has_post_thumbnail()) { ?>
				<div class="event-thumb">
					<?php the_post_thumbnail('widget-thumb'); ?>
				</div>
				<?php } ?>
				<div class="event-content">
					<h5 class="event-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
					<p class="event-meta"><?php echo $icare_event_date; ?></p>
				</div>
			</li>
			<?php endwhile; endif; ?>
		</ul>
		</div>
		
		<?php
		wp_reset_postdata(); 
		echo $after_widget;
    }

	/**
	 * Handles the setup of the form to be used in the WordPress admin.
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Stores the form values when they are saved in the admin.
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
        
        
    /**
     * Simply swaps two indexes in an array.
     * 
     * @param array $old_array
     * @param int $first_index
     * @param int $second_index
     * 
     * @returns array $new_array
     */
    private function swap_indexes( $old_array, $first_index, $second_index ) {
        //copy the original array
        $new_array = $old_array;
        
        //overwrite the indexes with each other
        $new_array[$first_index] = $old_array[$second_index];
        $new_array[$second_index] = $old_array[$first_index];
        
        return $new_array;
    }
    	
    private function get_rss_feed() {
    }
}