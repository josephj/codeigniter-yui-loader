<?php
/**
 * Use this configuration file to make relationship
 * between modules and static (CSS/JavaScript) files.
 * You must also specify module dependencies here.
 *
 * The static_loader library will transform this configuration file
 * to an YUI config.
 */
$config = array();
//=================
// Groups
//=================
/**
 * Before setting this, you should understand the group attribute in YUI config.
 *
 * Reference: http://yuilibrary.com/yui/docs/api/classes/config.html#property_groups
 *
 * NOTE - We add a magic 'serverComboCSS' attribute.
 *        Set it to true if you want all belonging CSS files
 *        being combined and loaded with traditional link tag approach,
 *        instead of using dynamic scripting.
 */
$config["groups"] = array(

    // For miiiCasa customized libraries.
    "mui" => array(
        "async"          => FALSE,
        "combine"        => TRUE,
        "serverComboCSS" => FALSE, // Load CSS on-the-fly.
        "root"           => "lib/mui/",
        "lang"           => array("en-US", "zh-TW"),
    ),

    // For miiiCasa index application.
    "index" => array(
        "async"          => FALSE,
        "combine"        => TRUE,
        "serverComboCSS" => TRUE, // Load CSS using <link>
        "root"           => "index/",
        "lang"           => array("en-US", "zh-TW"),
    ),
);

//=================
// Modules
//=================
/**
 * Individual module setting.
 * You should specify its belonging group, related CSS & JS files,
 * and dependent modules.
 */
$config["modules"] = array(
    //=================
    // MUI Modules
    //=================
    "platform-core" => array(
        "group"    => "mui",
        "js"       => "platform/core.js",
        "requires" => array(
            "node-base",
            "event-base",
            "platform-sandbox",
        ),
    ),
    "platform-sandbox" => array(
        "group" => "mui",
        "js"    => "platform/sandbox.js",
    ),
    "lang-service" => array(
        "group"    => "mui",
        "js"       => "platform/lang_service.js",
        "requires" => array(
            "platform-core", "platform-sandbox", "intl",
        ),
    ),
    "mui-cssbutton" => array(
        "group"    => "mui",
        "css"      => "cssbutton/assets/skins/miiicasa/cssbutton-skin.css",
        "requires" => array("cssbutton"),
    ),
    "scroll-pagination" => array(
        "group"    => "mui",
        "js"       => "scroll-pagination/scroll-pagination.js",
        "css"      => "scroll-pagination/assets/scroll-pagination.css",
        "requires" => array(
            "event", "event-resize", "node-event-delegate",
            "datasource",
        ),
    ),
    "editable" => array(
        "group"    => "mui",
        "js"       => "editable/editable.js",
        "css"      => "editable/assets/skins/miiicasa/editable.js",
        "requires" => array(
            "base", "panel", "event-mouseenter",
            "event-delegate", "node-event-delegate",
            "io-base", "escape", "intl"
        ),
    ),
    //===========================
    // DIV Modules (welcome)
    //===========================
    "welcome" => array(
        "group"    => "index",
        "js"       => "welcome/welcome.js",
        "lang"     => array("en-US", "zh-TW"),
        "requires" => array(
            "platform-core", "platform-sandbox", "lang-service",
        ),
    ),
    "welcome/_notification" => array(
        "group"    => "index",
        "js"       => "welcome/_notification.js",
        "css"      => "welcome/_notification.css",
        "requires" => array(
            "substitute", "scroll-pagination", "panel",
            "node-event-delegate", "handlebars",
        ),
    ),
    //===========================
    // DIV Modules (common)
    //===========================
    "common/_masthead" => array(
        "group"    => "index",
        "js"       => "common/_masthead.js",
        "css"      => "common/_masthead.css",
        "requires" => array(),
    ),
);

?>
