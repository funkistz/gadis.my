<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Process Intro plugin
 */
class BAMobileIntroPluginCore{
    /**
     * Screen Id
     */
    public $screen_id;

    /**
     * validate intro
     */
    public $valid;

    /**
     * Intros
     */
    public $intros;

    /**
     * Start construct
     */
    public function __construct($intros = array()){
        /**
         * Non support with wordpress version < 3.3
         */
        if( get_bloginfo( 'version' ) < '3.3' )
            return;

        /**
         * Get screen id
         */
        $screen = get_current_screen();
        $this->screen_id = $screen->id;

        /**
         * Register intro
         */
        $this->bamobile_register_intros( $intros );

        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    public function init_hooks(){
        add_action( 'admin_enqueue_scripts', array( $this, 'bamobile_add_intros' ), 1000 );
        add_action( 'admin_print_footer_scripts', array( $this, 'bamobile_add_scripts' ) );
    }

     /**
     * Register the available intros for the current screen
     */
    public function bamobile_register_intros( $intros ){
        $screen_intros = null;
        foreach( $intros as $intro ) {
            if( $intro['screen'] == $this->screen_id ){
                $options = array(
                    'content'  => '<h3> '.$intro['title'].' </h3> <p> '.$intro['content'].' </p>',
                    'position' => $intro['position']
                );
                $screen_intros[$intro['id']] = array(
                    'screen'  => $intro['screen'],
                    'target'  => $intro['target'],
                    'bamobile_type' => $intro['bamobile_type'],
                    'options' => $options
                );
            }
        }
        $this->intros = $screen_intros;
    }

    /**
     * Add intros to the current screen if they were not dismissed
     */
    public function bamobile_add_intros() {
        if( !$this->intros || !is_array( $this->intros ) )
            return;

        // Get dismissed intros
        $get_dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
        $dismissed = explode( ',', (string) $get_dismissed );
        // Check intros and remove dismissed ones.
        $valid_intros = array( );
        foreach( $this->intros as $intro_id => $intro ){
            if(
                in_array( $intro_id, $dismissed ) 
                || empty( $intro ) 
                || empty( $intro_id ) 
                || empty( $intro['target'] ) 
                || empty( $intro['options'] )
            )
                continue;
            $intro['pointer_id'] = $intro_id;
            $valid_intros['pointers'][] = $intro;
        }
        if( empty( $valid_intros ) )
            return;

        $this->valid = $valid_intros;
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
    }

     /**
     * Print JavaScript if intros are available
     */
    public function bamobile_add_scripts(){
        if( empty( $this->valid ) )
            return;
        $intros = json_encode( $this->valid );
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                var style = "<style type='text/css'>.mobiconnector-intro-ul{list-style:disc !important;padding: 0px 15px 0px 36px;}</style>";
                jQuery('head').append(style);
                var intros =  <?php echo $intros;  ?>;
                var ajaxurl = '<?php echo MOBICONNECTOR_AJAX_URL; ?>';
                var nonce = '<?php echo wp_create_nonce('bamobile-clear-intros'); ?>';
                var indexPosition = 0;

                /**
                 * Block intros
                 */
                function bamobile_block_intros(indexPosition){
                    options = jQuery.extend(intros.pointers[indexPosition].options,{
                        close:function(){
                            $.post( ajaxurl, {
                                action : 'bamobile_disable_tutorial_intros',
                                security : nonce
                            });
                        }
                    });
                    jQuery(intros.pointers[indexPosition].target).pointer(options).pointer('open');
                    jQuery('a.close').after('<a href="#" class="bamobile_next_tutorial_intros button-primary">Next</a>');
                    jQuery('a.close').css({
                        'float' : 'left',
                        'margin-left' :  '18px'
                    })
                    jQuery('a.bamobile_next_tutorial_intros').css({
                        'margin-right': '0px',
                        'margin-top': '-5px'
                    })                    
                    var parentIntros = $('a.bamobile_next_tutorial_intros').parent('.wp-pointer-buttons').parent('.wp-pointer-content').parent('.wp-pointer');
                    var thisElement = $(intros.pointers[indexPosition].target);
                    var position;
                    if(typeof thisElement.position() !== 'undefined'){
                        position = thisElement.position();
                        if(parentIntros.hasClass('wp-pointer-bottom')){
                            parentIntros.css({
                                'margin-top':'-18px'
                            })
                        }
                        if(intros.pointers[indexPosition].bamobile_type == 'menu_top'){
                            parentIntros.css({
                                'left': (position.left + 145) + 'px'
                            })
                        }
                        if(intros.pointers[indexPosition].bamobile_type == 'menu_right'){
                            parentIntros.css({
                                'left': (position.left - 142) + 'px'
                            })
                        }
                    }
                }

                bamobile_block_intros(indexPosition);

                jQuery('body').on('click', 'a.bamobile_next_tutorial_intros', function (e) {
                    e.preventDefault();
                    jQuery(this).parents('.wp-pointer').hide();
                    if ((indexPosition + 1) < intros.pointers.length) {
                        indexPosition++;
                    }else {
                        jQuery.post(ajaxurl, {
                            action : 'bamobile_disable_tutorial_intros',
                            security : nonce
                        });
                        return false;
                    }
                    bamobile_block_intros(indexPosition);
        
                });
            });
        </script>
        <?php
    }
}
?>