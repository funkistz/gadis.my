<?php
namespace DesignForm\Core;
    class BAFormCoreSetting {
        public $admin_pages = array();
        public $admin_subs = array();
        public $settings= array();
        public $sections= array();
        public $fields= array();
        public function __construct()
            {
                add_action('admin_init', array($this,'baform_register_customer_field'));
            }
        public function baform_register(){
            if(!empty($this->admin_pages)){
                add_action('admin_menu', array($this,'baform_addAdminMenu') );
            }
             if(!empty($this->settings)){
                add_action('admin_init', array($this,'baform_register_customer_field'));
            }
        }
        public function AddPage(array $page){
            $this->admin_pages = $page;
            return $this;
        }
        public function withSubPage( $title ){
            if(empty($this->admin_pages)){
                return $this;
            }
            $admin_page = $this->admin_pages[0];
            $sub_menu = array(
                array(
                    'parent_slug' => $admin_page['menu_slug'],
                    'page_title' => $admin_page['page_title'],
                    'menu_title' => ($title) ? $title :  $admin_page['menu_title'],
                    'capability' => $admin_page['capability'],
                    'menu_slug' => $admin_page['menu_slug'],
                    'callback' => $admin_page['callback'],
                )
                );
            $this->admin_subs = $sub_menu;
            return $this;
        }
        public function addSubadmin(array $pages){
             $this->admin_subs = array_merge($this->admin_subs,$pages);
            return $this;
        }
        public function baform_addAdminMenu(){
            foreach ($this->admin_pages as $page) {
                add_menu_page( $page['page_title'],  $page['menu_title'],  $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position'] );
            }
            foreach ($this->admin_subs as $page) {
                add_submenu_page( $page['parent_slug'],$page['page_title'],  $page['menu_title'],  $page['capability'], $page['menu_slug'], $page['callback']);
            }
        }
        public function baform_setSetting(array $settings){
            $this->settings = $settings;
            return $this;
        }
        public function baform_setSection(array $sections){
            $this->sections = $sections;
            return $this;
        }
        public function baform_setField(array $fields){
            $this->fields = $fields;
            return $this;
        }
        public function baform_register_customer_field(){
            foreach ($this->settings as $setting) {
                register_setting( $setting["option_group"], $setting["option_name"], (isset($setting["callback"]) ? $setting["callback"] : '' ));
            }
            //Them 1 section setting
            foreach ($this->sections as $section) {
                add_settings_section( $section["id"], $section["title"], (isset($section["callback"]) ? $section["callback"] : ''), $section["page"] );
            }
            //Them 1 feild setting
            foreach ($this->fields as $field) {
                add_settings_field($field["id"], $field["title"], (isset($field["callback"]) ? $field["callback"] : ''), $field["page"] , $field["section"], (isset($field["args"]) ? $field["args"] : ''));
            }
        }
    }
?>