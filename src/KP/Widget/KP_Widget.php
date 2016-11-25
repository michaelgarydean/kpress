<?php
    
    class KP_Widget extends KP_Entity {
        protected $description;
        
        public function __construct( $label, $description ) {
            
            $this->description = __( $description, 'kp' );
            
            //save the label of the feed and create a machine name to reference it by
            parent::__construct( $label );
            
            add_action( 'widgets_init', array ($this, 'create_widget' ) );
        }
        
        /**
         * Create a widget object and register it with Wordpress.
         * 
         * The KP_Widget class is a subclass of the WP_Widget object, part
         * of the Wordpress API.
         */
        public function create_widget() {
            $widget = new KP_Widget_Shell( $this->label, $this->machine_name, $this->description );
            
            register_widget( $widget );
        }
        
        /**
         * The output of the widget. 
         * 
         * Assigns a template to the widget used to define the widgets output on the
         * front-end.
         * 
         * @params String $template_name
         */
        public function set_template( $template_name ) {
            
        }
    }