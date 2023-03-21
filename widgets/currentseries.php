<?php

namespace WPC\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


class Currentseries extends Widget_Base{

  public function get_name(){
    return 'currentseries';
  }

  public function get_title(){
    return 'Currentseries';
  }

  public function get_icon(){
    return 'fa fa-camera';
  }

  public function get_categories(){
    return ['general'];
  }

  protected function _register_controls(){
   
  }
  

  protected function render(){
    
  }

  protected function _content_template(){
    ?>
   
        <?php
  }
}
