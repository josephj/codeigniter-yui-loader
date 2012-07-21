<?php

if ( ! defined("BASEPATH"))
{
    exit("No direct script access allowed");
}

/**
 * Read the static config to generate inline YUI config.
 *
 * @class Static_loader
 */
class Static_Loader {

    private $_static_config;
    private $_user_modules;
    private $_css_files;
    private $_groups;

    public function __construct()
    {
        $this->config =& load_class("Config");
        $this->CI =& get_instance();
    }

    /**
     * Non-recursively collect all required modules.
     *
     * @method _get_require_modules
     * @private
     * @param module {Array} The module name list.
     * @return {Array} The require modules.
     */
    private function _get_require_modules($modules)
    {
        $config = $this->config->item("static");
        $require_modules = array();
        foreach ($modules as $module)
        {
            $require_modules[] = $module;
            $target = $config["modules"][$module];
            if ( ! isset($target) || ! isset($target["requires"]))
            {
                continue;
            }
            $require_modules = array_merge($require_modules, $target["requires"]);
        }
        return array_unique($require_modules);
    }

    /**
     * Recursively collect all required modules based on _get_require_modules.
     *
     * @method _get_all_require_modules
     * @private
     * @param module {Array} The module name list.
     * @return {Array} The require modules.
     */
    private function _get_all_require_modules($modules)
    {
        $current_modules = array();
        $require_modules = $modules;
        while ( ! $is_end)
        {
            $required_modules = $this->_get_require_modules($require_modules);
            if ($current_modules === $required_modules)
            {
                $is_end = TRUE;
            }
            else
            {
                $current_modules = $required_modules;
            }
        }
        return $required_modules;
    }

    /**
     * Get JavaScript config for a single module.
     *
     * @param $module {Array} The original module configuration.
     */
    private function _get_js_config($module)
    {
        $data = array();
        foreach ($module as $key => $data)
        {
            if (isset($module["js"]))
            {
                $data = array();
                if (isset($module["js"]))
                {
                    $data["path"] = $module["js"];
                }
                if (isset($module["lang"]))
                {
                    $data["lang"] = $module["lang"];
                }
                if (isset($module["async"]))
                {
                    $data["async"] = $module["async"];
                }
                if (isset($module["requires"]))
                {
                    $data["requires"] = $module["requires"];
                }
            }
            else if (isset($module["css"]))
            {
                continue;
            }
            else
            {
                $data[$key] = $data;
            }
        }
        return $data;
    }

    /**
     * Transform our customized configuration (config/static.php)
     * to YUI config.
     *
     * @param $modules {Array} The module name list.
     * @return {String} The output HTML code.
     */
    private function _get_html($css_files, $groups)
    {
        $html   = array();
        if (count($css_files))
        {
            $html[] = '<link rel="stylesheet" href="combo/?g=css&f=' . implode(",", $css_files) . '">';
        }
        $html[] = '<script src="combo/?g=js"></script>';
        $config = array(
            "filter"   => "raw",
            "async"    => FALSE,
            "combine"  => TRUE,
            "comboBase"=> "combo/?f=",
            "comboSep" => ",",
            "root"     => "lib/yui/build/",
            "langs"     => "zh-TW,en-US",
            "groups"   => $groups,
        );
        $html[] = "<script>YUI_config = " . json_encode($config) . ";</script>";
        $html[] = "<script>YUI().use(\"" . implode("\",\"", $this->_user_modules) . "\");</script>";
        return implode("\n", $html);
    }

    public function load($modules)
    {
        $groups = array();
        $css_files = array();
        $is_end = FALSE;
        $this->config->load("static", TRUE);
        $this->_user_modules = $modules;
        $this->_static_config = $this->config->item("static");
        $config = $this->config->item("static");

        // Set group basic data.
        foreach ($config["groups"] as $name => $data)
        {
            $groups[$name] = array(
                "combine"  => $data["combine"],
                "fetchCSS" => !($data["serverComboCSS"]),
                "root"     => $data["root"],
                "lang"     => $data["lang"],
                "modules"  => array(),
            );
        }

        // Get all required modules.
        $modules = array_keys($this->_static_config["modules"]);

        foreach ($modules as $module_name)
        {
            // Ignore modules which are not defined in configuration.
            // e.g. The YUI native modules.
            if ( ! array_key_exists($module_name, $config["modules"]))
            {
                continue;
            }

            $module = $config["modules"][$module_name];
            $group_name = $module["group"];
            $group = $groups[$group_name];

            if (isset($module["js"]))
            {
                $groups[$group_name]["modules"][$module_name] =
                    $this->_get_js_config($module);
            }

            if (isset($module["css"]))
            {
                // Check if belonging group uses CSS server combo.
                $server_combo = $config["groups"][$group_name]["serverComboCSS"];

                if (isset($module["js"]))
                {
                    if ($server_combo)
                    {
                        $css_files[] = $group["root"] . $module["css"];
                    }
                    else
                    {
                        // Add a new module which related with javascript.
                        $group["modules"]["$module_name-css"] = array(
                            "path" => $module["css"],
                            "type" => "css",
                        );
                        $group["modules"][$module_name]["requires"][] = $require_module-css;
                    }
                }
                else
                {
                    if ($server_combo)
                    {
                        $css_files[] = $group["root"] . $module["css"];

                        if (isset($module["requires"]))
                        {
                            $group["modules"][$module_name]["requires"] = $module["requires"];
                        }
                        else
                        {
                            // Remove this module.
                            for ($i = count($this->_user_modules) - 1, $j = 0; $i >= $j; $i--)
                            {
                                if ($this->user_modules[$i] === $module_name)
                                {
                                    array_splice($this->user_modules, $i, 1);
                                }
                            }
                        }
                    }
                    else
                    {
                        $groups[$group_name]["modules"][$module_name] = array(
                            "path" => $module["css"],
                            "type" => "css",
                        );
                    }
                }
            }
        }
        return $this->_get_html($css_files, $groups);

    }
}
/* End of file Static_Loader.php */
