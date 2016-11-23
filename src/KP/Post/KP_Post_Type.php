<?php

    namespace KP\Post;
    use KP\KP_Entity;
    
    abstract class KP_Post_Type extends KP_Entity {
        protected $taxonomies;
        
        protected $metaboxes;
        protected $text_editors;
        
        private $post_args;
        private $labels;
        
        public function __construct( $label )  {
            //save the label of the post type and create a machine name to reference it by
            parent::__construct( $label );
        }
        
        /**
         * Add metabox(es) to the post type.
         * 
         * Accepts either a KP_Metabox object or an array of KP_Metabox objects.
         * 
         * @TODO Metaboxes should not have their priority and section assigned here. They should be assigned
         *  in the constructor of a metabox object.
         * @TODO All metaboxes should not have to be added at once. Currently, they don't, the hook is assigned 
         *  everytime the function is run though. Does this have implications? Check Wordpress API.
         * @TODO Set-up form validation
         * 
         * @see KP_Metabox
         * @see KP_Post_Type::assign_metabox
         * @see https://developer.wordpress.org/reference/functions/add_meta_box/
         * 
         * @param KP_Metabox|array $metaboxes   Either a KP_Metabox object or an array of KP_Metabox objects to be added to the post type.
         * 
         * @return void
         */
        public function add_metabox( $metaboxes ) {
            //replace the metaboxes array if input is an array, or create a new array if singular
            if( is_array($metaboxes) ) {
                $this->metaboxes = $metaboxes;
            } else {
                $this->metaboxes[] = $metaboxes;
            }
            
            //add metaboxes to post type
            add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_form_validation' ) );
        }
        
        /**
         * Tell Wordpress to register all the metaboxes assigned to this post type.
         * 
         * @return void
         */
        public function register_metaboxes() {
            
            //go through each metabox and assign it to the post type
            foreach( $this->metaboxes as $metabox ) {
                
                add_meta_box(
                    $metabox->machine_name,                 //machine name to reference metabox
                    __( $metabox->label, 'kp' ),            //label of metabox, internationalized
                    array($metabox, 'display_content'),     //callback function to display content in the metabox
                    $this->machine_name,                    //name of the post type to add metabox to
                    $metabox->section,                      //context/location to display metabox
                    $metabox->priority                      //priority within the context/location
                );
            }
        }
        
        /**
         * Removes metabox(es) from the post type.
         * 
         * @TODO Implement function
         */
        public function remove_metabox() {
            
        }
        
        /**
         * Validate form input for all metaboxes, custom fields and input to the custom post type.
         * 
         * @TODO Implement function.
         * 
         */        
        public function enqueue_form_validation() {
            
        }
    }