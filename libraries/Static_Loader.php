<?php
if ( ! defined("BASEPATH"))
{
    exit("No direct script access allowed");
}
/**
 * Read the static config to generate inline YUI config.
 *
 *    $this->load->library("static_loader");
 *    $this->static_module->set("common/_masthead", "home/_notification");
 *    $data["loader_html"] = $this->static_module->load();
 *
 * @class Static_loader
 */
class Static_Loader {

    public $yui_config;
    public $css_files;
    public $user_modules;

    public function __construct()
    {
        $this->config =& load_class("Config");
        $this->CI =& get_instance();
    }

    /**
     * Get YUI JavaScript module config.
     *
     * @param $module {Array} The static module configuration.
     * @return {Array} The YUI JavaScript module config.
     */
    private function _get_js_config($module)
    {

        if ( ! isset($module["js"])) {
            return FALSE;
        }

        // Move 'js' attribute to 'path'
        // to keep align with YUI config.
        $data = array();
        if (isset($module["js"]))
        {
            $data["path"] = $module["js"];
            unset($module["js"]);
        }

        // List the attributes which should be attached.
        $allows = array("lang", "async", "requires");
        foreach ($module as $key => $value)
        {
            if (in_array($key, $allows))
            {
                $data[$key] = $value;
            }
        }
        $data["type"] = "js";
        return $data;
    }

    /**
     * Get the loader HTML.
     *
     *     echo $this->static_loader->load();
     *
     * @return {String} The loader HTML code.
     */
    public function load()
    {
        $html       = array();
        $config     = $this->yui_config;
        $css_files  = $this->css_files;
        $modules    = implode("\",\"", $this->user_modules);
        $tpl_link   = '<link rel="stylesheet" href="combo/?g=css&f=%s">';
        $tpl_script = array(
            '<script type="text/javascript" src="combo/?g=js"></script>',
            '<script type="text/javascript">YUI_config = %s;</script>',
            '<script>YUI().use("' . $modules . '");</script>',
        );
        $tpl_script = implode("\n", $tpl_script);

        if (count($css_files))
        {
            $html[] = sprintf($tpl_link, implode(",", $css_files));
        }
        $html[] = sprintf($tpl_script, json_encode($config));
        return implode("\n", $html);
    }

    /**
     * Set modules you want use.
     *
     *    $this->static_module->set("common/_masthead", "home/_notification");
     *
     * @method set
     * @public
     */
    public function set($modules)
    {
        if (gettype(func_get_arg(0)) === "string")
        {
            $modules = func_get_args();
        }
        $this->user_modules = $modules;

        // Load configuration file - config/static.php.
        $this->config->load("static", TRUE);
        $config = $this->config->item("static");

        // Make groups config.
        $groups = array();
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

        // The CSS files which needs to be combined.
        $css_files = array();

        // Make 'groups' config ready.
        $modules = array_keys($config["modules"]);
        foreach ($modules as $module_name)
        {
            // Ignore modules which are not defined in configuration.
            // e.g. The YUI native modules.
            if ( ! array_key_exists($module_name, $config["modules"]))
            {
                continue;
            }

            $module     = $config["modules"][$module_name];
            $group_name = $module["group"];
            $group      = $groups[$group_name];

            // Attach JavaScript config.
            if (isset($module["js"]))
            {
                $groups[$group_name]["modules"][$module_name] =
                    $this->_get_js_config($module);
            }

            // Stop here and continue to next iteration
            // if no css attribute exists.
            if ( ! isset($module["css"]))
            {
                continue;
            }

            // Check if belonging group uses CSS server combo.
            $server_combo = !($group["fetchCSS"]);
            if ($server_combo)
            {
                $css_files[] = $group["root"] . $module["css"];
                if (
                    ! isset($module["js"]) &&
                    in_array($module_name, $this->user_modules)
                )
                {
                    $offset = array_search($module_name, $this->user_modules);
                    unset($this->user_modules[$offset]);
                }
            }
            else
            {
                if (isset($module["js"]))
                {
                    $group["modules"][$module_name]["requires"][] = "$module_name-css";
                    $module_name = "$module_name-css";
                }
                $groups[$group_name]["modules"][$module_name] = array(
                    "path" => $module["css"],
                    "type" => "css",
                );
            }
        }

        $this->css_files  = $css_files;

        // TODO - Make these attribute configurable.
        $this->yui_config = array(
            "filter"    => "raw",
            "async"     => FALSE,
            "combine"   => TRUE,
            "comboBase" => "combo/?f=",
            "comboSep"  => ",",
            "root"      => "lib/yui/build/",
            "langs"     => "zh-TW,en-US",
            "groups"    => $groups,
        );
    }
}
/* End of file Static_Loader.php */
