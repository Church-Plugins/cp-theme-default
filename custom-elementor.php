<?php
namespace WPC;

// use Elementor\Plugin; ?????

class Widget_Loader{

  private static $_instance = null;

  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  private function include_widgets_files(){
    error_reporting(0);
    require_once(__DIR__ . '/widgets/herobanner.php');
    require_once(__DIR__ . '/widgets/latestmessage.php');
    require_once(__DIR__ . '/widgets/currentseries.php');
  }

  public function register_widgets(){

    $this->include_widgets_files();

    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\Herobanner());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\Latestmessage());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\Currentseries());

  }

  public function __construct(){
    add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets'], 99);
  }
}

// Instantiate Plugin Class
Widget_Loader::instance();
