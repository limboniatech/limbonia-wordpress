<?php
namespace Limbonia\WordPress;

class Widget extends \WP_Widget
{
  /**
   * The plugin object that controls this widget
   *
   * @var \Limbonia\WordPress\Plugin
   */
  protected $oPlugin = null;

  /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
  {
    $sPluginClass = preg_replace("/Widget$/", 'Plugin', get_class($this));
    $this->oPlugin = new $sPluginClass;
		parent::__construct
    (
			$this->oPlugin->pluginId() . '_widget',
			__($this->oPlugin->pluginName() . ' Widget', $this->oPlugin->textDomain()),
			['description' => __($this->oPlugin->pluginDescription(), $this->oPlugin->textDomain())]
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $hArgs
	 * @param array $hInstance
	 */
	public function widget($hArgs, $hInstance)
  {
    echo $this->generateWidget($hArgs, $hInstance);
	}

  public function generateWidget($hArgs, $hInstance)
  {
    return '';
  }

  /**
	 * Outputs the options form on admin
	 *
	 * @param array $hInstance The widget options
	 */
	public function form($hInstance)
  {
    echo $this->generateForm($hInstance);
	}

  public function generateForm($hInstance)
  {
    return '';
  }
}