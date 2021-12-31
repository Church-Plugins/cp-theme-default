<?php

namespace WPC\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


class Herobanner extends Widget_Base{

  public function get_name(){
    return 'herobanner';
  }

  public function get_title(){
    return 'Herobanner';
  }

  public function get_icon(){
    return 'fa fa-camera';
  }

  public function get_categories(){
    return ['general'];
  }

  protected function _register_controls(){
    error_reporting(0);
    $this->start_controls_section(
      'section_content',
      [
        'label' => 'Settings',
      ]
    );

    $this->add_control( 
      'label_heading',
      [
        'label' => 'Label Heading',
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => 'My Example Heading'
      ]
    );

    $this->add_control(
      'content_heading',
      [
        'label' => 'Content Heading',
        'type' => \Elementor\Controls_Manager::TEXT,
        'default' => 'My Other Example Heading'
      ]
    );

    $this->add_control(
      'content',
      [
        'label' => 'Content',
        'type' => \Elementor\Controls_Manager::WYSIWYG,
        'default' => 'Some example content. Start Editing Here.'
      ]
    );

    $this->add_control(
      'image',
      [
        'label' => 'Image',
        'type' => \Elementor\Controls_Manager::MEDIA,
        'default' => 'Image'
      ]
    );

    $this->end_controls_section();
  }
  

  protected function render(){
    $settings = $this->get_settings_for_display();

    $this->add_inline_editing_attributes('label_heading', 'basic');
    $this->add_render_attribute(
      'label_heading',
      [
        'class' => ['herobanner__label-heading'],
      ]
    );
    //print_r( $settings );
    ?>
    <div class="herobanner" style="background-image: url(<?php echo $settings['image']['url'] ?>)">
      <div <?php echo $this->get_render_attribute_string('label_heading'); ?>>
        <?php echo $settings['label_heading']?>
      </div>
      <div class="herobanner__content">
        <div class="herobanner__content__heading" <?php echo $this->get_render_attribute_string('content_heading'); ?>>
          <?php echo $settings['content_heading'] ?>
        </div>
        <div class="herobanner__content__copy" <?php echo $this->get_render_attribute_string('content'); ?>>
          <?php echo $settings['content'] ?>
        </div>
      </div>
    </div>
    <?php
  }

  protected function _content_template(){
    ?>
    <#
      view.addInlineEditingAttributes( 'label_heading', 'basic' );
      view.addRenderAttribute(
        'label_heading',
        {
            'class': [ 'herobanner__label-heading' ],
        }
      );
    #>
    <div class="herobanner" style="background-image: url({{{ settings.image.url }}})">
      <div {{{ view.getRenderAttributeString( 'label_heading' ) }}}>{{{ settings.label_heading }}}</div>
      <div class="herobanner__content">
        <div class="herobanner__content__heading">{{{ settings.content_heading }}}</div>
        <div class="herobanner__content__copy">
          {{{ settings.content }}}
        </div>
      </div>
    </div>
        <?php
  }
}
