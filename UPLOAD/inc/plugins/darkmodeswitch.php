<?php

// Disallow direct access to this file for security reasons
if (!defined('IN_MYBB'))
{
    die('Direct initialization of this file is not allowed.');
}

if (defined('THIS_SCRIPT'))
{
    global $templatelist;

    if (isset($templatelist))
    {
        $templatelist .= ',';
    }

    if (THIS_SCRIPT == 'usercp.php')
    {
        $templatelist .= 'usercp_options_darkmodeswitch';
    }
}

if (!defined('IN_ADMINCP'))
{
    $plugins->add_hook('usercp_options_end', 'darkmodeswitch_usercp_options');
    $plugins->add_hook('usercp_do_options_end', 'darkmodeswitch_usercp_do_options');
    $plugins->add_hook('global_intermediate', 'darkmodeswitch_global');
}

function darkmodeswitch_info()
{
    return array(
        'name'          => 'MyBB Darkmode Switch',
        'description'   => 'Include a setting into UserCP to switch dark mode CSS files in forum',
        'website'       => 'https://github.com/SvePu/MyBB-Darkmode-Switch',
        'author'        => 'SvePu',
        'authorsite'    => 'https://github.com/SvePu',
        'version'       => '1.0',
        'codename'      => 'darkmodeswitch',
        'compatibility' => '18*'
    );
}

function darkmodeswitch_install()
{
    global $db, $mybb;

    if (!$db->field_exists("darkmode", "users"))
    {
        $db->add_column("users", "darkmode", "tinyint(1) NOT NULL DEFAULT '0'");
    }

    $templatearray = array(
        'usercp_options_darkmodeswitch' => '<tr>
<td colspan="2"><span class="smalltext">{$lang->darkmode}</span></td>
</tr>
<tr>
<td colspan="2">
    <select name="darkmode">
        <option value="2" {$dm_auto_selected}>{$lang->darkmode_auto}</option>
        <option value="1" {$dm_enabled_selected}>{$lang->darkmode_enabled}</option>
        <option value="0" {$dm_disabled_selected}>{$lang->darkmode_disabled}</option>
    </select>
</td>
</tr>'
    );

    foreach ($templatearray as $name => $template)
    {
        $template = array(
            'title' => $db->escape_string($name),
            'template' => $db->escape_string($template),
            'version' => $mybb->version_code,
            'sid' => -2,
            'dateline' => TIME_NOW
        );

        $db->insert_query('templates', $template);
    }

    /** Add Stylesheets */
    $tid = 1;

    $stylesheetarray = array(
        'darkmode.css' => "",
        'darkmode_auto.css' => "@media (prefers-color-scheme: dark) { }"
    );

    foreach ($stylesheetarray as $name => $styles)
    {
        $stylesheet = array(
            'name' => $db->escape_string($name),
            'tid' => (int)$tid,
            'attachedto' => 'darkmode.php',
            'stylesheet' => $styles,
            'cachefile' => $db->escape_string(str_replace('/', '', $name)),
            'lastmodified' => TIME_NOW
        );

        $sid = $db->insert_query("themestylesheets", $stylesheet);

        require_once MYBB_ROOT . $mybb->config['admin_dir'] . '/inc/functions_themes.php';

        if (!cache_stylesheet($stylesheet['tid'], $stylesheet['cachefile'], $stylesheet['stylesheet']))
        {
            $db->update_query("themestylesheets", array('cachefile' => "css.php?stylesheet={$sid}"), "sid='{$sid}'", 1);
        }
    }

    update_theme_stylesheet_list($tid, false, true);
}

function darkmodeswitch_is_installed()
{
    global $db;
    return $db->field_exists('darkmode', 'users');
}

function darkmodeswitch_uninstall()
{
    global $db;
    if ($db->field_exists('darkmode', 'users'))
    {
        $db->drop_column('users', 'darkmode');
    }

    $db->delete_query('templates', "title IN ('usercp_options_darkmodeswitch')");

    /** Remove Stylesheet */
    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $db->delete_query("themestylesheets", "name IN ('darkmode.css', 'darkmode_auto.css')");

    $query = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($query))
    {
        update_theme_stylesheet_list($theme['tid']);
    }
}

function darkmodeswitch_activate()
{
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
    find_replace_templatesets('usercp_options', '#' . preg_quote('{$board_language}') . '#', "{\$board_darkmode}\n{\$board_language}");
}

function darkmodeswitch_deactivate()
{
    require MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('usercp_options', '#' . preg_quote("{\$board_darkmode}\n") . '#', '');
}

function darkmodeswitch_usercp_options()
{
    global $lang, $templates, $user, $board_darkmode;
    $lang->load('darkmodeswitch');

    $dm_auto_selected = $dm_enabled_selected = $dm_disabled_selected = '';
    if (isset($user['darkmode']) && $user['darkmode'] == 2)
    {
        $dm_auto_selected = "selected=\"selected\"";
    }
    elseif (isset($user['darkmode']) && $user['darkmode'] == 1)
    {
        $dm_enabled_selected = "selected=\"selected\"";
    }
    else
    {
        $dm_disabled_selected = "selected=\"selected\"";
    }

    eval("\$board_darkmode = \"" . $templates->get("usercp_options_darkmodeswitch") . "\";");
}

function darkmodeswitch_usercp_do_options()
{
    global $db, $mybb, $user, $config, $theme;

    $update_array = array(
        'darkmode' => $mybb->get_input('darkmode', MyBB::INPUT_INT)
    );

    $db->update_query("users", $update_array, "uid = '" . $user['uid'] . "'");
}

function darkmodeswitch_global()
{
    global $mybb, $theme, $stylesheets;

    if (!isset($mybb->user['darkmode']) || (isset($mybb->user['darkmode']) && $mybb->user['darkmode'] == 0))
    {
        return;
    }

    $sh_name = '';
    switch ($mybb->user['darkmode'])
    {
        case 1:
            $sh_name = 'darkmode.css';
            break;
        case 2:
            $sh_name = 'darkmode_auto.css';
            break;
    }

    if ($mybb->settings['minifycss'])
    {
        $sh_name = str_replace('.css', '.min.css', $sh_name);
    }

    $sh_path = 'cache/themes/theme' . $theme['tid'] . '/' . $sh_name;

    if (!file_exists(MYBB_ROOT . $sh_path))
    {
        $sh_path = 'cache/themes/theme1/' . $sh_name;
    }

    if (file_exists(MYBB_ROOT . $sh_path))
    {
        $sh_path .= "?t=" . filemtime(MYBB_ROOT . $sh_path);
    }

    $sh_url = $mybb->settings['bburl'] . '/' . $sh_path;

    $stylesheets .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"{$sh_url}\" />\n";
}
