<?php
namespace Limbonia\WordPress;

class Plugin
{
  protected static $bLimboniaSettingsInit = false;
  protected $sPluginDescription = '';
  protected $sNameSpace = '';
  protected $sPluginID = '';
  protected $sPluginDir = '';
  protected $sTextDomain = '';
  protected $sSettingsName = '';
  protected $sSettingsGroup = '';
  protected $sPluginName = '';
  protected $hEnqueue =
  [
    'front' =>
    [
      'script' => [],
      'style' => []
    ],
    'back' =>
    [
      'script' => [],
      'style' => []
    ],
    'both' =>
    [
      'script' => [],
      'style' => []
    ]
  ];
  protected $hSettingMethods = null;

  /**
   * An array of options that will be used in various places in this plugin
   *
   * @var array
   */
  protected $hOptions = null;

  public function __construct()
  {
    $aClass = explode('\\', get_class($this));

    //get rid of the class name
    array_pop($aClass);

    //save the namespace
    if (empty($this->sNameSpace))
    {
      $this->sNameSpace = implode('\\', $aClass);
    }

    //remove "WordPress"
    unset($aClass[1]);

    if (empty($this->sPluginID))
    {
      $this->sPluginID = implode('_', $aClass);
    }

    if (empty($this->sPluginDir))
    {
      $this->sPluginDir = 'limbonia' . strtolower(preg_replace("#([A-Z])#", "-$1", $aClass[2]));
    }

    if (empty($this->sTextDomain))
    {
      $this->sTextDomain = $this->sPluginID . '_TextDomain';
    }

    if (empty($this->sSettingsName))
    {
      $this->sSettingsName = $this->sPluginID . '_Settings';
    }

    if (empty($this->sSettingsGroup))
    {
      $this->sSettingsGroup = $this->sSettingsName . '_Group';
    }

    if (empty($this->sPluginName))
    {
      $aClass[2] = preg_replace("#([A-Z])#", " $1", $aClass[2]);
      $this->sPluginName = ucwords(implode(' ', $aClass));
    }
  }

  /**
   * Return the options
   *
   * @return array
   */
  public function getOptions()
  {
    if (is_null($this->hOptions))
    {
      $this->hOptions = get_option($this->sSettingsName);
    }

    return $this->hOptions;
  }

  public function getOption($sName)
  {
    return get_option($this->sPluginID . '_setting_' . $sName);
  }

  protected function getCheckSetting($sName)
  {
    return "<input name=\"{$this->sPluginID}_setting_{$sName}\" id=\"{$this->sPluginID}_setting_{$sName}\" type=\"checkbox\" value=\"1\" " . checked('1', $this->getOption($sName), false) . ">\n";
  }

  protected function getTextSetting($sName)
  {
    echo "<input type=\"text\" name=\"{$this->sPluginID}_setting_{$sName}\" id=\"{$this->sPluginID}_setting_{$sName}\" value=\"" . $this->getOption($sName) . "\" />";
  }

  public function pluginId()
  {
    return $this->sPluginID;
  }

  public function pluginName()
  {
    return $this->sPluginName;
  }

  public function pluginDescription()
  {
    return $this->sPluginDescription;
  }

  public function textDomain()
  {
    return $this->sTextDomain;
  }

  public function settingsName()
  {
    return $this->sSettingsName;
  }

  public function settingsGroup()
  {
    return $this->sSettingsGroup;
  }

  public function initSettings()
  {
    if (is_admin())
    {
      add_action('admin_init', function()
      {
        register_setting($this->sSettingsGroup, $this->sSettingsName);
      });
    }
  }

  public function adminMenu()
  {
    if (is_admin())
    {
      add_action('admin_menu', function()
      {
        add_options_page
        (
          $this->sPluginName . ' Settings',
          $this->sPluginName,
          'manage_options',
          $this->sPluginID . '_settings',
          [$this, 'optionsContent']
        );
      });
    }
  }

  /**
   * Generate the actual options form
   */
  public function optionsContent()
  {
    echo "
      <div class=\"wrap\">
        <h2>" . __($this->sPluginName . ' Settings', $this->sTextDomain) . "</h2>
        <p>" . __('Settings for the ' . $this->sPluginName . ' plugin', $this->sTextDomain) . "</p>
        <form method=\"post\" action=\"options.php\">\n";
    settings_fields($this->sSettingsGroup);
    echo $this->generateOptionsContent();
    echo "          <p class=\"submit\"><button type=\"submit\" name=\"submit\" id=\"submit\" class=\"button button-primary\">" . __('Save Changes', $this->sTextDomain) . "</button></p>
        </form>
      </div>\n";
  }

  public function generateOptionsContent()
  {
    return '';
  }

  public function enqueueFiles()
  {
    $aFrontStyle = array_merge($this->hEnqueue['front']['style'], $this->hEnqueue['both']['style']);
    $aFrontScript = array_merge($this->hEnqueue['front']['script'], $this->hEnqueue['both']['script']);

    if (!empty($aFrontStyle) || !empty($aFrontScript))
    {
      add_action('wp_enqueue_scripts', function() use($aFrontStyle, $aFrontScript)
      {
        if (!empty($aFrontStyle))
        {
          foreach ($aFrontStyle as $sStyleFile)
          {
            wp_enqueue_style($this->sPluginID . '-' . basename($sStyleFile, '.css') . '-style', plugins_url() . "/$this->sPluginDir/css/" . basename($sStyleFile));
          }
        }

        if (!empty($aFrontScript))
        {
          foreach ($aFrontScript as $xScript)
          {
            if (is_string($xScript))
            {
              wp_enqueue_script($this->sPluginID . '-' . basename($xScript, '.js') . '-script', plugins_url() . "/$this->sPluginDir/js/" . basename($xScript));
            }
            elseif (is_array($xScript))
            {
              $sScript = array_shift($xScript);
              wp_enqueue_script($this->sPluginID . '-' . basename($sScript, '.js') . '-script', plugins_url() . "/$this->sPluginDir/js/" . basename($sScript), $xScript);
            }
          }
        }
      });
    }

    if (!is_admin())
    {
      return;
    }

    $aBackStyle = array_merge($this->hEnqueue['back']['style'], $this->hEnqueue['both']['style']);
    $aBackScript = array_merge($this->hEnqueue['back']['script'], $this->hEnqueue['both']['script']);

    if (!empty($aBackStyle) || !empty($aBackScript))
    {
      add_action('admin_enqueue_scripts', function() use ($aBackStyle, $aBackScript)
      {
        if (!empty($aBackStyle))
        {
          foreach ($aBackStyle as $sStyleFile)
          {
            wp_enqueue_style($this->sPluginID . '-' . basename($sStyleFile, '.css') . '-style', plugins_url() . "/$this->sPluginDir/css/" . basename($sStyleFile));
          }
        }

        if (!empty($aBackScript))
        {
          foreach ($aBackScript as $xScript)
          {
            if (is_string($xScript))
            {
              wp_enqueue_script($this->sPluginID . '-' . basename($xScript, '.js') . '-script', plugins_url() . "/$this->sPluginDir/js/" . basename($xScript));
            }
            elseif (is_array($xScript))
            {
              $sScript = array_shift($xScript);
              wp_enqueue_script($this->sPluginID . '-' . basename($sScript, '.js') . '-script', plugins_url() . "/$this->sPluginDir/js/" . basename($sScript), $xScript);
            }
          }
        }
      });

    }
  }

  public function registerWidget($sWidget = 'Widget')
  {
    $sWidget = empty($sWidget) ? 'Widget' : (string)$sWidget;
    add_action('widgets_init', function() use ($sWidget)
    {
      register_widget($this->sNameSpace . "\\$sWidget");
    });
  }

  public function getWidgetNumber($sWidgetId)
  {
    return (integer)preg_replace("/[^0-9]/", '', str_replace(strtolower($this->sPluginID) . '_widget', '', $sWidgetId));
  }

  public function getWidgetData($iWidgetNumber)
  {
    $hWidget = get_option('widget_' . strtolower($this->sPluginID) . '_widget');

    if (!isset($hWidget[$iWidgetNumber]))
    {
      throw new \Exception("Widget type '$this->sPluginID' does not contain ID #$iWidgetNumber");
    }

    return $hWidget[$iWidgetNumber];
  }

  public function addSettings()
  {
    if (!is_admin())
    {
      return;
    }

    $aMethods = get_class_methods($this);

    if (is_null($this->hSettingMethods))
    {
      $this->hSettingMethods = [];

      foreach ($aMethods as $sMethod)
      {
        if (preg_match("/add(.*?)Setting([A-Z].+)/", $sMethod, $aMatch))
        {
          $sPage = empty($aMatch[1]) ? 'general' : strtolower($aMatch[1]);

          if (!isset($this->hSettingMethods[$sPage]))
          {
            $this->hSettingMethods[$sPage] = [];
          }

          $this->hSettingMethods[$sPage][] =
          [
            'method' => $sMethod,
            'title' => preg_replace("#([A-Z])#", " $1", $aMatch[2]),
            'name' => $this->sPluginID . '_setting' . strtolower(preg_replace("#([A-Z])#", "_$1", $aMatch[2]))
          ];
        }
      }
    }

    if (empty($this->hSettingMethods))
    {
      return;
    }

    if (isset($this->hSettingMethods['limbonia']) && !self::$bLimboniaSettingsInit)
    {
      self::$bLimboniaSettingsInit = true;
      add_action('admin_menu', function()
      {
        add_options_page
        (
          'Limbonia',
          'Limbonia',
          'manage_options',
          'limbonia-settings-page',
          function()
          {
            echo '<h1>Limbonia Settings</h1>';
            echo '<form method="post" action="options.php">';
            do_settings_sections('limbonia-settings-page');
            settings_fields('limbonia-settings');
            submit_button();
            echo '</form>';
          }
        );
      });
    }

    add_action('admin_init', function()
    {
      foreach ($this->hSettingMethods as $sPage => $aSettingList)
      {
        switch ($sPage)
        {
          case 'limbonia':
            $sPluginName = preg_replace("/Limbonia /", '', $this->sPluginName);
            $sPluginDesc = empty($this->sPluginDesc) ? "Settings for $sPluginName" : $this->sPluginDesc;
            add_settings_section
            (
              $this->sPluginID . '_setting_section',
              $sPluginName,
              function() use($sPluginDesc)
              {
                echo "<p>$sPluginDesc</p>";
              },
              'limbonia-settings-page'
            );

            foreach ($aSettingList as $hSetting)
            {
              add_settings_field
              (
                $hSetting['name'],
                $hSetting['title'],
                [$this, $hSetting['method']],
                'limbonia-settings-page',
                $this->sPluginID . '_setting_section'
              );

              register_setting('limbonia-settings', $hSetting['name']);
            }
            break;

          default:
            add_settings_section
            (
              $this->sPluginID . '_setting_section',
              $this->sPluginName,
              function()
              {
                echo "<p>Settings for $this->sPluginName</p>";
              },
              $sPage
            );

            foreach ($aSettingList as $hSetting)
            {
              add_settings_field
              (
                $hSetting['name'],
                $hSetting['title'],
                [$this, $hSetting['method']],
                $sPage,
                $this->sPluginID . '_setting_section'
              );

              register_setting($sPage, $hSetting['name']);
            }
            break;
        }
      }
    });
  }
}