<?php

    /**
     * Custom fields created for metaboxes.
     * 
     * @TODO Extend functionality for use without metaboxes, and for widgets.
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
         * @param String $input_type            The type of HTML element to display for this field. 
         *                                      Possible values include checkbox, text, date, radio, textarea, number, email.
         * @param String[] $input_values        The options to display for an element with multiple options (for example, with checkboxes)
         * @param Csnqc_Metabox $parent_entity  The metabox object this field belongs to.
         */
        public function __construct( $label, $input_type = 'text', $input_values = array(), $parent_entity = null ) {
            
            //the parent is used primarily for naming purposes - sets the prefix of the object
            $this->parent_metabox = $parent_entity;
            $this->input_type = strtolower( $input_type) ;
            $this->input_values = $input_values;
            $this->set_caption("");
            
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
         * Create HTML to display the label, input and caption for custom field.
         * 
         * This function does not actually create data in the database, but rather
         *  calls another function that does, KP_Custom_Field::create_input_element().
         * Typically, this function ends up getting called when a KP_Metabox object is added
         *  to a KP_Post_Type or KP_Custom_Post_Type object using add_metabox();
         * 
         * @TODO Make this function compatible with more than metaboxes.
         * 
         * @see KP_Post_Type::add_metabox()
         * @see KP_Post_Type::register_metabox()
         * @see KP_Metabox::create_content()
         * @see KP_Metabox::create_fields()
         * @see KP_Custom_Field::create_fields()
         * @see KP_Custom_Field::create_input_element()
         */
        public function create_field( $post ) {
            //Initialize HTML returned by the function
            $field_html = "";
            
            /**
             * Create a <label> element - the title on top of the box
             */
            $label_html = sprintf( '<label for="%s">%s</label>',
                            $this->machine_name, $this->label );            
                            
            /**
             * Create <input> element(s) - the field itself and the post metadata
             */
            $input_html = $this->create_input_element( $post );
            
            /**
             * Create a <span> element to store the caption.
             */
            $span_html = sprintf( '<span class="description">%s</span>',
                            $this->get_caption() );

            /**
             * Format the checkbox input type so the the checkbox is output before the label.
             */
            if ( 'checkbox' === $this->input_type ) {
                //nest elements between <p> tags
                $field_html = sprintf( '<p>%s%s%s<p>', $input_html, $label_html, $span_html );
            } else {
                //nest elements between <p> tags
                $field_html = sprintf( '<p>%s%s%s<p>', $label_html . "<br />", $input_html, $span_html );
            }
            
            //Output all the HTML associated with a field.
            echo $field_html;
        }
        
        /**
         * Create HTML for an input element and populate it with existing data
         *  from the database.
         * 
         * The function ends up getting called when fields are created using 
         *  KP_Custom_Field::create_field().
         * Supports input types: text, radio, checkbox, date, number
         * 
         * @TODO Make this function compatible with more than metaboxes.
         * @TODO Throw an error on final return
         * 
         * @see KP_Post_Type::add_metabox()
         * @see KP_Post_Type::register_metabox()
         * @see KP_Metabox::create_content()
         * @see KP_Metabox::create_fields()
         * @see KP_Custom_Field::create_field()
         * @see KP_Custom_Field::create_input_element()
         */
        public function create_input_element( $post ) {
            
            /**
             * Initialize variables.
             * 
             * $checked     Marks a checkbox as checked.
             * $required    Designates a field as required. For use with the jQuery form validatation plugin.
             */
            $checked = "";
            $required = "";
            
            //@TODO Needs testing. Does this produce the expected results?
            if( true === $this->required ) {
                $required = 'required';
            }
            
            /**
             * Retrieve the current value(s) of the field from the database as an array.
             * 
             * If it's only a single value, convert it to a string.
             */
            $stored_value = get_post_meta( $post->ID, $this->machine_name, false );
            
            if( count( $stored_value ) < 2 ) {
                if( isset( $stored_value[0] ) ) {
                    $stored_value = esc_attr( $stored_value[0] );
                } else {
                    $stored_value = "";
                }
            }
            
            /**
             * Create an input of type text if none of the other input types are selected.
             */
            switch( $this->input_type ) {
                
                /**
                 * @TODO How this works with input values needs to be documented!!
                 * @TODO Error checking for the format of the array
                 */
                case 'range':
                    
                    $range_html = "";
                    
                    if( !is_null( $this->input_values[ 'min' ] ) && !is_null( $this->input_values[ 'max' ] ) ) {
                        $range_html = sprintf( 
                            "<input type='range' name='%s' min='%s' max='%s' value=%s>", 
                            $this->machine_name, 
                            $this->input_values[ 'min' ], 
                            $this->input_values[ 'max' ],
                            $stored_value
                        );
                    }
                    
                    return $range_html;
                    
                case 'select':
                    $options_html = "";
                    
                    foreach( $this->input_values as $option ) {
                        $option_html = sprintf( '<option value="%s" %s>%s</option>', strtolower( $option ), selected( strtolower( $option ), $stored_value, false ), $option );
	                	$options_html = $options_html . $option_html;
                    }
                    
                    return sprintf( '<select id="%s" name="%s">%s</select>', $this->machine_name, $this->machine_name, $options_html );
                
                /**
                 * @TODO
                 * This doesn't function properly yet. Need to figure out how to properly save the data and deal with it when it's not a single value.
                 */
                case 'select multiple':
                    $options_html = "";
                    
                    
                    foreach( $this->input_values as $option ) {
                        $option_html = sprintf( '<option value="%s" %s>%s</option>', strtolower( $option ), selected( strtolower( $option ), $stored_value, false ), $option );
	                	$options_html = $options_html . $option_html;
                    }
                    
                    return sprintf( '<select id="%s" name="%s[]" multiple>%s</select>', $this->machine_name, $this->machine_name, $options_html );          
                /**
                 * Use the built-in Wordpress datepicker as an interface for choosing dates.
                 */                       
                case 'date':
                    return sprintf('<input class="date-picker" id="%s" type="date" name="%s" value="%s" %s data-rule-dateISO="true" />',
                                    $this->machine_name, $this->machine_name, $stored_value, $required ); 
                /**
                 * Create radio buttons for each of the provided input values.
                 */                    
                case 'radio':
                    
                    //Initialize HTML output variable
                    $radio_buttons_html = '';

                    //If input values isn't set, or doesn't have any elements, don't try to create any input elements.
                    if( count( $this->input_values ) < 1 ) {
                        return;
                    }
                    
                    foreach( $this->input_values as $key => $name ) {
                        $index = $key + 1;
                        
                        /**
                         * In order to validate a set of checkboxes with jQuery validate plugin, the first checkbox must be marked as required.
                         */
                        if( $key === 0 ) {
                            
                            //Create the first radio button - mark as required if neccessary
                            $radio_button = sprintf( '<input type="%s" name="%s" id="%s" value="%s" %s %s />', 
                                    $this->input_type, $this->machine_name, $this->machine_name . "_" . $index, $index, checked( $stored_value, $index, false ), $required );    
                        
                        } else {
                            
                            //Not the first radio button in the group - don't mark as required
                            $radio_button = sprintf( '<input type="%s" name="%s" id="%s" value="%s" %s />', 
                                    $this->input_type, $this->machine_name, $this->machine_name . "_" . $index, $index, checked( $stored_value, $index, false ) );                                
                        
                        }
                        
                                
                        //Create the label for the button etc
                        $span_html = sprintf( "<span>%s</span>", $name );
                        
                        //Create a <label> element - the title on top of the box
                        $label_html = sprintf( '<label for="%s">%s%s</label><br />',
                                        $this->machine_name . "_" . $index, $radio_button, $span_html );
                        
                        $radio_buttons_html = $radio_buttons_html . $label_html;
                    }
                    
                    return $radio_buttons_html;
                
                /**
                 * Create checkbox elements that can be marked as 'checked', if designated in the database.
                 */                           
                case 'checkbox':
                    
                    return sprintf( '<input type="%s" name="%s" id="%s" value="%s" %s/>', 
                                $this->input_type, $this->machine_name, $this->machine_name, $stored_value, checked( $stored_value, 1, false ) );

                /**
                 * Create textarea input element.
                 */   
                case 'textarea':
                    
                    return sprintf( '<textarea rows=4 style="width:98%%" name="%s" id="%s" %s>%s</textarea>', 
                                $this->machine_name, $this->machine_name, $required, $stored_value );
                /**
                 * Create number input element.
                 */                  
                case 'number':
                    
                /**
                 * Create email input element.
                 */
                case 'email':
                    
                /**
                 * Create a generic input element.
                 * 
                 * 'text' is the default input_type when the object is created.
                 */
                default:
                    return sprintf( '<input type="%s" name="%s" id="%s" size="30" style="width:98%%" value="%s" %s />', 
                                $this->input_type, $this->machine_name, $this->machine_name, $stored_value, $required );
            }//end switch
            
            /**
             * Something went wrong. Just return an empty string.
             * 
             * @TODO more robust error handling
             */
            return "";
            
        } //end create_input_element
        
        public function get_caption() {
            return $this->caption;
        }
    }