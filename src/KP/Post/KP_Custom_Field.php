<?php

    /**
     * Custom fields created for metaboxes.
     *
     * @package  kp-custom-post-types
     */

    /**
     * Provides functionality to manage Wordpress custom fields and associated 
     * data, such as interface labels.
     * 
     * Custom fields are intended for use within Wordpress metaboxes (using the 
     * KP_Metabox object), typically present inside custom post types.
     * 
     * @author   Michael Dean <mike@koumbit.org>
     * @version  1.0
     * @access   public
     * 
     * @see KP_Custom_Post_Type
     * @see KP_Media_Post_Type
     * @see KP_Metabox
     * 
    */
    
    namespace KP\Post;
    
    use KP\Post;
    use KP\KP_Entity;
    
    class KP_Custom_Field extends KP_Entity {
        /**
         * @var KP_Metabox $parent_metabox      The metabox object this field belongs to.
         * @var String $caption                 Some text to display in a <span> element under the field.
         */
        private $parent_metabox, $caption;
        
        /**
         * @var String $input_type              The type of HTML element to display for this field. 
         *                                      Possible values include checkbox, text, date, radio, textarea, number, email.
         * @var String[] $input_values          The options to display for an element with multiple options (for example, with checkboxes)
         */
        public $input_type, $input_values;
        
        /**
         * @var Boolean $required               Whether or not this is a required field (used for form validation).
         */
        public $required;
        
        /**
         * Create a custom field to be used within a {@link Csnqc_Metabox metabox}.
         * 
         * The constructor links a Csnqc_Metabox object to the custom field, creating
         *  parent-child relationship. The input type, as well as any neccessary values,
         *  are also stored, later used to display the custom field in the Wordpress interface.
         *  Finally, the standard naming functions are called by calling the object's parent
         *  constructor.
         * 
         * @TODO Remove $parent_entity as required on constructor (could be called from outside metabox )
         * @TODO Add $caption as option in constructor. 
         * 
         * @param String $label                 The title of the taxonomy used in the user interface.
         * @param Csnqc_Metabox $parent_entity  The metabox object this field belongs to.
         * @param String $input_type            The type of HTML element to display for this field. 
         *                                      Possible values include checkbox, text, date, radio, textarea, number, email.
         * @param String[] $input_values        The options to display for an element with multiple options (for example, with checkboxes)
         */
        public function __construct( $label, $parent_entity, $input_type, $input_values ) {
            
            //the parent is used primarily for naming purposes - sets the prefix of the object
            $this->parent_metabox = $parent_entity;
            $this->input_type = strtolower($input_type);
            $this->input_values = $input_values;
            $this->set_caption("");
            
            //prefix with the parents name
            $this->prefix = $parent_entity->machine_name;
            
            //save the label of the post type and create a machine name to reference it by
            parent::__construct( $label, $parent_entity );
        }
        
        /**
         * Sets the text description that is displayed just underneath the custom field.
         * 
         * @param String $caption   Text to display under the custom field.
         */
        public function set_caption( $caption ) {
            $this->caption = $caption;
        }
        
        /**
         * Output HTML for a custom field.
         * 
         * HERRREE+++++++++++++++++++++++++++++++++
         */
        public function display_field( $post ) {
            
            //create html elements and populate with the metabox data
            
            //create a <label> element - the title on top of the box
            $label_html = sprintf( '<label for="%s">%s</label>',
                            $this->machine_name, $this->label );            
                            
            //create <input> element(s) - the field itself
            $input_html = $this->create_input_element( $post );
            
            //create a <span> element to store the caption
            $span_html = sprintf( '<span class="description">%s</span>',
                            $this->get_caption() );
            
            //format the checkbox input type so the the box goes before the label
            if ( 'checkbox' === $this->input_type ) {
                //nest elements between <p> tags
                $form_html = sprintf( '<p>%s%s%s<p>', $input_html, $label_html, $span_html );
            } else {
                //nest elements between <p> tags
                $form_html = sprintf( '<p>%s%s%s<p>', $label_html . "<br />", $input_html, $span_html );
            }
            
            //output the html
            echo $form_html;
        }
        
        //Support input types include: text, radio, checkbox, date, number
        //@TODO Throw an error on final return
        public function create_input_element( $post ) {
            
            //initialize variables that may be empty
            $checked = "";
            $output_html = "";
            $required = "";
            
            if( isset($this->required) && $this->required === true ) {
                $required = 'required';
            }
                
            //retrieve the current value of the field from the database
            $stored_value = esc_attr( get_post_meta( $post->ID, $this->machine_name, true ) );
            
            //create an input of type text if none of the other input tyeps are selected
            switch( $this->input_type ) {
                
                case 'date':
                    return sprintf(' <input class="date-picker" id="%s" type="date" name="%s" value="%s" %s data-rule-dateISO="true" />',
                                    $this->machine_name, $this->machine_name, $stored_value, $required ); 
                    
                case 'radio':
                    
                    //create radio buttons
                    foreach( $this->input_values as $key=>$name ) {
                        $index = $key + 1;
                        
                        //for use with jQuery validate plugin - mark first item as required to force validation
                        if( $key === 0 ) {
                            //create a radio button - mark as checked
                            $radio_button = sprintf( '<input type="%s" name="%s" id="%s" value="%s" %s %s />', 
                                    $this->input_type, $this->machine_name, $this->machine_name . "_" . $index, $index, checked( $stored_value, $index, false ), $required );    
                        } else {
                            //create a radio button - mark as checked
                            $radio_button = sprintf( '<input type="%s" name="%s" id="%s" value="%s" %s />', 
                                    $this->input_type, $this->machine_name, $this->machine_name . "_" . $index, $index, checked( $stored_value, $index, false ) );                                
                        }
                        
                                
                        //Create the label for the button etc
                        $span_html = sprintf( "<span>%s</span>", $name );
                        
                        //create a <label> element - the title on top of the box
                        $label_html = sprintf( '<label for="%s">%s%s</label><br />',
                                        $this->machine_name . "_" . $index, $radio_button, $span_html );
                        
                        $output_html = $output_html . $label_html;
                    }
                    
                    return $output_html;
                    
                case 'checkbox':
                    
                    return sprintf( '<input type="%s" name="%s" id="%s" value="%s" %s/>', 
                                $this->input_type, $this->machine_name, $this->machine_name, $stored_value, checked( $stored_value, 1, false ) );
                case 'textarea':
                    return sprintf( '<textarea rows=4 style="width:98%%" name="%s" id="%s" %s>%s</textarea>', 
                                $this->machine_name, $this->machine_name, $required, $stored_value );
                                
                case 'number':
                case 'email':
                default:
                    return sprintf( '<input type="%s" name="%s" id="%s" size="30" style="width:98%%" value="%s" %s />', 
                                $this->input_type, $this->machine_name, $this->machine_name, $stored_value, $required );
            }//end switch
            
            //input_type was not valid or supported
            //maybe throw an error...?
            return "";
            
        } //end create_input_element
        
        public function get_caption() {
            return $this->caption;
        }
    }