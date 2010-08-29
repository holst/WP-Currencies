<?php
/*
Plugin Name: WordPress Auto Currencies
Plugin URI: http://currencies.dk/wordpress/
Description: Automatically turn currency-formatted text into a link to currencies.dk, showing its value in several other currencies.
Version: 0.1
Author: Jonathan Holst
Author URI: http://holst.biz/
*/

// This plugin is released in the public domain. You may used it for whatever 
// purpose you see fit.

class Currencies {
    static $pattern = '([\d.]+?)\ ([A-Z]{3})';
    
    private $settings = array(
        // Should any currency declaration be enclosed in some sort of 
        // parenthetical structure? This can prevent accidental linking.
        'enclose_pattern' => true,
        // If the previous is true, what to enclose with?
        'enclose_pattern_with' => array('[[', ']]'),
        // Should some site in the future come in and replicate currencies.dk's
        // API, let's make it easy for people to make use of that instead. This 
        // could also potentially be used if, say, someone wanted to write "USD
        // 1" instead of "1 USD".
        'uri' => 'http://currencies.dk/$1/$2/'
    );
    
    public function __construct($settings=array()) {
        $this->delimiter = substr(self::$pattern, 0, 1);
        
        foreach($settings as $setting => $value) {
            if(array_key_exists($setting, $this->settings)) {
                $this->settings[$setting] = $value;
            } else {
                trigger_error('Unknown setting '.$setting, E_USER_NOTICE);
            }
        }
    }
    
    private function cb($matches) {
        $uri = str_replace(
            array('$1', '$2'), 
            array($matches[1], $matches[2]), 
            $this->settings['uri']
        );
        
        $text = str_replace(
            array(
                $this->settings['enclose_pattern_with'][0],
                $this->settings['enclose_pattern_with'][1]
            ),
            '',
            $matches[0]
        );
        
        return '<a href="'.$uri.'">'.$text.'</a>';
    }
    
    public function replace($str) {
        $pattern = '/'.self::$pattern.'/';
        
        if($this->settings['enclose_pattern']) {
            $pattern = '/';
            $pattern.= preg_quote($this->settings['enclose_pattern_with'][0]);
            $pattern.= self::$pattern;
            $pattern.= preg_quote($this->settings['enclose_pattern_with'][1]);
            $pattern.= '/';
        }
        
        return preg_replace_callback($pattern, array($this, 'cb'), $str);
    }
}

function wp_currencies($content) {
    $c = new Currencies;
    
    return $c->replace($content);
}

add_filter('the_excerpt', 'wp_currencies');
add_filter('the_content', 'wp_currencies');