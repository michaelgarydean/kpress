<?php

    /**
     * A child class of the Csnqc_Post_Type.
     *
     * @package  csnqc-custom-post-types
     */

    /**
     * Extends Csnqc_Post_Type with functionality more specific to Wordpress 
     *  custom post types, such as content access control using an associated 
     *  taxonomy, modification of the "Les miens" text when a user is viewing 
     *  posts of a certain post type, support for excerpts, and individual
     *  label modifications.
     *  
     *  Like other files in the {@link csnqc-custom-post-types} package, it is intended for
     *  French language users and has not been properly localized.
     * 
     * @author   Michael Dean <mike@koumbit.org>
     * @version  1.0
     * @access   public
     * 
     * @see Csnqc_Post_Type
     * @see Csnqc_Media_Post_Type
     * @see Csnqc_Taxonomy
    */

    namespace KP\Post;
    
    use KP;
    use KP\Post;
    
    class KP_Custom_Post_Type extends KP_Post_Type {
        /**
         * @var mixed[] $args               Arguments for the register_post_type() Wordpress function.
         * @var String[] $capabilities      A list of unique strings thay are associated with Wordpress capabilities.
         *                                  This allows for the post type's content to be restricted by a taxonomy that
         *                                  can be assigned to a user {@link @Csnqc_Taxonomy}
         * @var Boolean $run_label_update   Indicates whether specific labels for the post type have been modified,
         *                                  and that the database should be updated to reflect the changes.
         *                                  {@link update_post_labels()}
         */
        protected $args, $capabilities, $run_label_update;
        
        /**
         * Set-up labels, define capability names and modify the "Les miens" text
         *  for the custom post type. Also adds hook actions for access control 
         *  (map_meta_cap), for registering the post type, and for updating post labels
         *  after the post type has been registered.
         * 
         * @param String $label     The main label used to reference the post type,
         *                          and shown on the user interface.
         * @param string $slug      The string used in the URL to reference the post type.
         *                          See Wordpress documentation for more in depth discussion.
         */
        public function __construct( $label, $slug = null ) {
            
            //save the label of the post type and create a machine name to reference it by
            parent::__construct( $label );
            
            //additional options for the content type - used for the Wordpress register_post_type()
            $this->args = array(
                'label'            => $this->label,
                'menu_position'     => 5,
                'public'            => true,
                'supports'          => array( 'title', 'editor', 'thumbnail', 'revisions', 'author' ),
                'has_archive'       => true,
                'can_export'        => true
            );
            
            //set the slug for the post type
            $this->set_slug( $slug );
            
            //register the post type
            add_action( 'init', array( $this, 'add_post_type' ) );
        }
        
        /**
         * Register the custom post type with Wordpress (saves to the database).
         * 
         * @return void
         */        
        public function add_post_type() {
            register_post_type( $this->machine_name, $this->args );
        }
        
        /**
         * Remove the content editor from the custom post type.
         * 
         * The function de-registers the initial action, updates the options
         * for the post type, and then registers it again with Wordpress.
         * 
         * @see KP_Custom_Post_Type::__constructor()
         * 
         * @return void
         */
        public function remove_content_editor() {
            //deregister the action registered in the constructor
            remove_action( 'init', array( $this, 'add_post_type' ) );
            
            //find the index of the editor option
            $index = array_search('editor', $this->args['supports'] );
            
            //if it exists, remove it from the supported options
            if( $index !== false ){
                unset( $this->args['supports'][$index]);
            }
            
            //re-register the action with Wordpress
            add_action( 'init', array( $this, 'add_post_type' ) );
        }
        
        /**
         * Set the slug to be used with the custom post type.
         * 
         * Called by the constructor.
         * 
         * @TODO Should this be in KP_Post_Type? Is it useful for the media post type?
         * 
         * @see KP_Custom_Post_Type::__constructor()
         * 
         */
        public function set_slug( $slug ) {
            
            //check if the post type has already been registered
            if ( null !== $slug ) {
                
                if ( true == has_action( 'init', array( $this, 'add_post_type' ) ) ) {
                    
                    //deregister the action registered in the constructor
                    remove_action( 'init', array( $this, 'add_post_type' ) );
                    
                    //assign a slug to the post-type, if provided
                    $this->args['rewrite'] = array( 'slug' => $slug );
                    
                    //re-register the action with Wordpress
                    add_action( 'init', array( $this, 'add_post_type' ) );
                    
                } else {
                    //assign a slug to the post-type, if provided
                    $this->args['rewrite'] = array( 'slug' => $slug );
                }
            }
        }
    }