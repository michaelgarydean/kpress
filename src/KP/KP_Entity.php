<?php

    /**
     * Sets up a standardized way of referencing all objects related to the framework.
     * 
     * The Csnqc_Entity class is basically just made up of naming functions. It
     *  creates a label and a machine name. We use a prefix in order to avoid
     *  naming collisions (in our case 'csnqc', defined in the parent class Csnqc). 
     *  If we are naming an entity that has a parent related to it, the parent already 
     *  has the prefix included, so we just add the child name on to the end of 
     *  its parent's name.
     * 
     * @package csnqc-theme-options
     * @todo Properly implement getters and setters for all class variables.
     */

    namespace KP;
    
    abstract class KP_Entity implements KP {
        /**
         * @var String $machine_name    The name used to store the entity in the database
         * @var String $label           The text displayed on the Wordpress admin interface for this entity
         * @var $string $prefix         A text prefix used to avoid naming collisions.
         */
        protected $machine_name, $label, $prefix;
        
        /**
         * @param string $label The label shown in the Wordpress user interface.
         * @param string $parent_entity The machine_name of the entity's parent.
         */
        public function __construct( $label, $parent_entity = null ) {
            //assign the name to the entity
            $this->label = $label;
            
            //format the name to use as the machine name
            if( null === $parent_entity ) {
                //assign the prefix to the entity
                $this->prefix = KP::text_domain;
            } else {
                //if there's a parent, prefix with the parents name
                $this->prefix = $parent_entity->machine_name;
            }
            
            //create the machine name for the entity
            $this->machine_name = $this->create_machine_name( $this->label );
        }
        
        /**
         * Generate a variable name (machine_name) to reference an object.
         * 
         * The $label is expected to contain capitals, spaces and even accents
         *  as it is used in the Wordpress user interface to label objects. The
         *  name returned by the function has all potentially problematic characters
         *  removed, and replaces spaces by underscores.
         * 
         * @param String $label             The label used to reference the object.
         *                                  Potentially displayed in the Wordpress user interface.
         * 
         * @return String $formatted_name   A string that is suitable to be used as a variable name.
         */
        private function create_machine_name( $label ) {
            //format the name with the prefix
            $formatted_name = sanitize_title( $this->prefix . '_' . $label );
            
            //replace the dashes with underscores
            $formatted_name = str_replace('-', '_', $formatted_name);
            
            return $formatted_name;
        }
        
        /**
         * A getter method to provide the object's machine-readable name.
         * 
         * @see create_machine_name()
         * 
         * @return String Csnqc_Entity::$machine_name   The object's machine-readable name.
         */
        public function get_machine_name() {
            return $this->machine_name;
        }
        
        /**
         * A setter method to set the object's machine-readable name.
         * 
         * @uses create_machine_name()
         * @see Csnqc_Entity::$label
         * 
         * @return void
         */
        public function set_machine_name( $label ) {
            $this->create_machine_name( $label );
        }
        
        /**
         * A getter method to provide the object's human-readable name.
         * 
         * @see Csnqc_Entity::$label
         * 
         * @return String Csnqc_Entity::$label   The object's machine-readable name.
         */
        public function get_label() {
            return $this->label;
        }
        
        /**
         * A setter method to set the object's human-readable name.
         * 
         * @see Csnqc_Entity::$label
         * 
         * @return void
         */
        public function set_label( $label ) {
            $this->label = $label;
        }
        
        /**
         * A callback function used by array_map to convert all the elements in
         *  an array to integer values. 
         * 
         * Used primarily for the user meta data taxonomy ids since they are stored
         *  as strings using the $_POST method.
         * 
         * @param Int|String $val   The string to convert to an integer value.
         * 
         * @return Int $val         The converted value as an Integer.
         */
        protected function convert_string_elements_to_int( $val ) {
            return intval( $val );
        }
        
        /**
         * Checks if the required plugin is active in network or single site.
         * 
         * @source http://queryloop.com/how-to-detect-if-a-wordpress-plugin-is-active/
         *
         * @param $plugin
         *
         * @return bool
         */
        public function plugin_is_active( $plugin ) {
        	$network_active = false;
        	if ( is_multisite() ) {
        		$plugins = get_site_option( 'active_sitewide_plugins' );
        		if ( isset( $plugins[$plugin] ) ) {
        			$network_active = true;
        		}
        	}
        	return in_array( $plugin, get_option( 'active_plugins' ) ) || $network_active;
        }
    }