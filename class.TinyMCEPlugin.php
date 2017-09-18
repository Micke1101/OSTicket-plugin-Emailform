<?php
require_once (INCLUDE_DIR . 'class.signal.php');
require_once ('config.php');

class TinyMCEPlugin extends Plugin {
    /**
     * Which config to use (in config.php)
     *
     * @var string
     */
    public $config_class = 'TinyMCEPluginConfig';
    
    /**
     * Run on every instantiation of osTicket..
     * needs to be concise
     *
     * {@inheritdoc}
     *
     * @see Plugin::bootstrap()
     */
    function bootstrap() {
        ob_start();
        register_shutdown_function(function () {
            $html = ob_get_clean();
            $javascript = file_get_contents(__DIR__ . '/tinymce-osticket.js');
            $javascript = $this->handleConfig($javascript);
            $html = preg_replace('/<script.*redactor.*<\/script>/', '', $html);
            print str_replace("</body>", $this->includeTinyMCE() . "<script>" 
            . $javascript 
            . "</script></body>", $html);
        });
    }
    
    function includeTinyMCE(){
        return "<script type=\"text/javascript\" src=\"" 
            . ROOT_PATH . "js/tinymce/tinymce.min.js\"></script>";
    }
    
    function handleConfig($html){
        $config = $this->getConfig();
        $html = str_replace("{TINYMCE_HEIGHT}", $config->get('height'), $html);
        $html = str_replace("{TINYMCE_THEME}", $config->get('theme'), $html);
        $html = str_replace("{TINYMCE_PLUGINS}", implode(' ', array_keys($config->get('plugins'))) . (($config->get('doautosave'))?" autosave":""), $html);
        $html = str_replace("{TINYMCE_MENUBAR}", (boolval($config->get('menubar')) ? 'true':'false'), $html);
        $html = str_replace("{TINYMCE_POWERED_BY}", (boolval($config->get('poweredby')) ? 'true':'false'), $html);
        if($config->get('doautosave')){
            $html = str_replace("{TINYMCE_AUTOSAVEOPTIONS}", "autosave_interval: \"" 
                . $config->get('autosaveinterval') . "s\",autosave_restore_when_empty: " 
                . (boolval($config->get('tryrestoreempty')) ? 'true':'false') 
                . ",autosave_retention: \"" . $config->get('autosaveretention') . "m\"", $html);
        }
        $html = str_replace("{TINYMCE_TOOLBAR}", $config->get('toolbar'), $html);
        return $html;
    }
    
    /**
     * Required stub.
     *
     * {@inheritdoc}
     *
     * @see Plugin::uninstall()
     */
    function uninstall() {
        $errors = array ();
        parent::uninstall ( $errors );
    }
    
    /**
     * Plugins seem to want this.
     */
    public function getForm() {
        return array ();
    }
}


