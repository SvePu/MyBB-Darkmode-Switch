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

    if (THIS_SCRIPT == 'member.php')
    {
        $templatelist .= 'member_register_darkmodeswitch';
    }
}

if (defined('IN_ADMINCP'))
{
    $plugins->add_hook('admin_config_settings_begin', 'darkmodeswitch_settings');
    $plugins->add_hook('admin_settings_print_peekers', 'darkmodeswitch_settings_peekers');
    $plugins->add_hook('admin_style_themes_edit_stylesheet_simple_commit', 'darkmodeswitch_update_stylesheet_simple');
    $plugins->add_hook('admin_style_themes_edit_stylesheet_advanced_commit', 'darkmodeswitch_update_stylesheet_advanced');
}
else
{
    $plugins->add_hook('usercp_options_end', 'darkmodeswitch_usercp_options');
    $plugins->add_hook('member_register_end', 'darkmodeswitch_member_register');
    $plugins->add_hook('usercp_do_options_end', 'darkmodeswitch_usercp_do_options');
    $plugins->add_hook('member_do_register_end', 'darkmodeswitch_member_do_register');
    $plugins->add_hook('global_intermediate', 'darkmodeswitch_global');
}

function darkmodeswitch_info()
{
    global $db, $lang;
    $lang->load('darkmodeswitch', true);

    return array(
        'name'          => 'MyBB Darkmode Switch',
        'description'   => $db->escape_string($lang->darkmodeswitch_desc),
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
    global $db, $mybb, $lang;

    /** Add DB Column */
    if (!$db->field_exists("darkmode", "users"))
    {
        $db->add_column("users", "darkmode", "tinyint(1) NOT NULL DEFAULT '2'");
    }

    /** Add Templates */
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
</tr>',
        'member_register_darkmodeswitch' => '<br />
<fieldset class="trow2">
<legend><strong><label for="darkmode">{$lang->darkmode}</label></strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" width="100%">
<tr>
<td colspan="2"><span class="smalltext">{$lang->darkmode_desc}</span></td>
</tr>
<tr>
<td>
<select name="darkmode"  id="darkmode">
    <option value="2" {$dm_auto_selected}>{$lang->darkmode_auto}</option>
    <option value="1" {$dm_enabled_selected}>{$lang->darkmode_enabled}</option>
    <option value="0" {$dm_disabled_selected}>{$lang->darkmode_disabled}</option>
</select>
</td>
</tr>
</table>
</fieldset>'
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
    $name = 'darkmode.css';
    $styles = @file_get_contents(__DIR__ . "/darkmodeswitch/{$name}");

    $query = $db->simple_select('themestylesheets', 'sid', "name='{$name}' AND tid='{$tid}'");
    if (!$db->fetch_field($query, 'sid') && isset($styles) && !empty($styles))
    {
        $styles = trim($styles);

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

        darkmodeswitch_create_auto_stylesheet($tid, $styles);

        update_theme_stylesheet_list($tid, false, true);
    }

    /** Add Settings */
    $multisettings = array(
        'general' => array(
            'darkmodeselector' => array(
                'optionscode' => 'yesno',
                'value' => 1
            ),
            'autodarkmodeguests' => array(
                'optionscode' => 'yesno',
                'value' => 1,
            )
        ),
        'member' => array(
            'darkmodeselectoronreg' => array(
                'optionscode' => 'yesno',
                'value' => 1
            ),
            'default_darkmode' => array(
                'optionscode' => "select\n2=" . $db->escape_string($lang->setting_default_darkmode_2) . "\n1=" . $db->escape_string($lang->setting_default_darkmode_1) . "\n0=" . $db->escape_string($lang->setting_default_darkmode_0),
                'value' => 2,
            )
        )
    );

    foreach ($multisettings as $settingsname => $settings)
    {
        $query = $db->simple_select('settinggroups', 'gid', "name='" . $settingsname . "'");
        $gid = (int)$db->fetch_field($query, 'gid');

        $query = $db->simple_select('settings', 'COUNT(*) AS disporder', "gid='{$gid}'");
        $disporder = (int)$db->fetch_field($query, 'disporder');
        ++$disporder;

        foreach ($settings as $key => $setting)
        {
            $setting['name'] = $db->escape_string($key);

            $lang_var_title = "setting_{$key}";
            $lang_var_description = "setting_{$key}_desc";

            $setting['title'] = $db->escape_string($lang->{$lang_var_title});
            $setting['description'] = $db->escape_string($lang->{$lang_var_description});
            $setting['disporder'] = $disporder;
            $setting['gid'] = $gid;

            $db->insert_query('settings', $setting);
            ++$disporder;
        }

        unset($gid, $disporder);
    }

    rebuild_settings();
}

function darkmodeswitch_is_installed()
{
    global $mybb;
    if (isset($mybb->settings['darkmodeselector']))
    {
        return true;
    }
    return false;
}

function darkmodeswitch_uninstall()
{
    global $db, $mybb;

    if ($mybb->request_method != 'post')
    {
        global $page, $lang;
        $lang->load('darkmodeswitch', true);

        $page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=darkmodeswitch', $lang->darkmodeswitch_uninstall_message, $lang->darkmodeswitch_uninstall);
    }

    $db->delete_query('templates', "title IN ('usercp_options_darkmodeswitch', 'member_register_darkmodeswitch')");

    $db->delete_query("settings", "name IN ('darkmodeselector', 'autodarkmodeguests', 'darkmodeselectoronreg', 'default_darkmode')");
    rebuild_settings();

    if (!isset($mybb->input['no']))
    {
        /** Remove Stylesheet */
        require_once MYBB_ROOT . $mybb->config['admin_dir'] . '/inc/functions_themes.php';

        $db->delete_query("themestylesheets", "name LIKE ('darkmode%')");

        $query = $db->simple_select("themes", "tid");
        while ($theme = $db->fetch_array($query))
        {
            darkmodeswitch_remove_stylesheets($theme['tid']);
            update_theme_stylesheet_list($theme['tid']);
        }

        if ($db->field_exists('darkmode', 'users'))
        {
            $db->drop_column('users', 'darkmode');
        }
    }
}

function darkmodeswitch_activate()
{
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
    find_replace_templatesets('usercp_options', '#' . preg_quote('{$board_language}') . '#', "{\$board_darkmode}\n{\$board_language}");
    find_replace_templatesets('member_register', '#' . preg_quote('{$boardlanguage}') . '#', "{\$boardlanguage}\n{\$boarddarkmode}");
    find_replace_templatesets('codebuttons', '#' . preg_quote('<script type="text/javascript">') . '#', "{\$theme['iconsscript']}\n<script type=\"text/javascript\">");
    find_replace_templatesets('codebuttons', '#' . preg_quote('format: "bbcode",') . '#', "format: \"bbcode\",{\$theme['icons']}");
}

function darkmodeswitch_deactivate()
{
    require MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('usercp_options', '#' . preg_quote("{\$board_darkmode}\n") . '#', '');
    find_replace_templatesets('member_register', '#' . preg_quote("\n{\$boarddarkmode}") . '#', '');
    find_replace_templatesets('codebuttons', '#' . preg_quote("{\$theme['iconsscript']}\n") . '#', '');
    find_replace_templatesets('codebuttons', '#' . preg_quote("{\$theme['icons']}") . '#', '');
}

function darkmodeswitch_settings()
{
    global $lang;
    $lang->load('darkmodeswitch', true);
}

function darkmodeswitch_settings_peekers(&$peekers)
{
    $peekers[] .= 'new Peeker($(".setting_darkmodeselector"), $("#row_setting_autodarkmodeguests"), 1, true)';
    $peekers[] .= 'new Peeker($(".setting_darkmodeselectoronreg"), $("#row_setting_default_darkmode"), 1, true)';
}

function darkmodeswitch_usercp_options()
{
    global $mybb, $board_darkmode;

    $board_darkmode = '';

    if ($mybb->settings['darkmodeselector'] != 1)
    {
        return;
    }

    global $lang, $templates, $user;
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
    global $mybb;

    if ($mybb->settings['darkmodeselector'] != 1)
    {
        return;
    }

    global $db, $user;

    $update_array = array(
        'darkmode' => $mybb->get_input('darkmode', MyBB::INPUT_INT)
    );

    $db->update_query("users", $update_array, "uid = '" . $user['uid'] . "'");
}

function darkmodeswitch_member_register()
{
    global $mybb, $boarddarkmode;

    $boarddarkmode = '';

    if ($mybb->settings['darkmodeselector'] != 1 || $mybb->settings['darkmodeselectoronreg'] != 1)
    {
        return;
    }

    global $lang, $templates, $theme;
    $lang->load('darkmodeswitch');

    $dm_auto_selected = $dm_enabled_selected = $dm_disabled_selected = '';
    if (isset($mybb->settings['default_darkmode']) && $mybb->settings['default_darkmode'] == 2)
    {
        $dm_auto_selected = "selected=\"selected\"";
    }
    elseif (isset($mybb->settings['default_darkmode']) && $mybb->settings['default_darkmode'] == 1)
    {
        $dm_enabled_selected = "selected=\"selected\"";
    }
    else
    {
        $dm_disabled_selected = "selected=\"selected\"";
    }

    eval("\$boarddarkmode = \"" . $templates->get("member_register_darkmodeswitch") . "\";");
}

function darkmodeswitch_member_do_register()
{
    global $mybb;

    if ($mybb->settings['darkmodeselector'] != 1 || $mybb->settings['darkmodeselectoronreg'] != 1)
    {
        return;
    }

    global $db, $user_info;

    $update_array = array(
        'darkmode' => $mybb->get_input('darkmode', MyBB::INPUT_INT)
    );

    $db->update_query("users", $update_array, "uid = '" . $user_info['uid'] . "'");
}

function darkmodeswitch_global()
{
    global $mybb, $theme;

    $theme['icons'] = $theme['iconsscript'] = '';

    if ($mybb->settings['darkmodeselector'] != 1)
    {
        return;
    }

    if (!$mybb->user['uid'] && $mybb->settings['autodarkmodeguests'] == 1)
    {
        $mybb->user['darkmode'] = 2;
    }

    if (isset($mybb->user['darkmode']))
    {
        $dm_stylesheet_name = '';
        switch ($mybb->user['darkmode'])
        {
            case 1:
                $dm_stylesheet_name = $theme['editortheme'] = 'darkmode.css';
                break;
            case 2:
                $dm_stylesheet_name = $theme['editortheme'] = 'darkmode_auto.css';
                break;
            default:
                return;
        }

        $theme['icons'] = ' icons: "darkmode",';
        $theme['iconsscript'] = '<script type="text/javascript" src="' . $mybb->settings['bburl'] . '/jscripts/sceditor/icons/darkmode.js?ver=' . $mybb->version_code . '"></script>';

        if ($mybb->settings['minifycss'])
        {
            $dm_stylesheet_name = str_replace('.css', '.min.css', $dm_stylesheet_name);
        }

        $dm_stylesheet_path = 'cache/themes/theme' . $theme['tid'] . '/' . $dm_stylesheet_name;

        if (!file_exists(MYBB_ROOT . $dm_stylesheet_path))
        {
            $dm_stylesheet_path = 'cache/themes/theme1/' . $dm_stylesheet_name;
        }

        if (file_exists(MYBB_ROOT . $dm_stylesheet_path))
        {
            $dm_stylesheet_path .= "?t=" . filemtime(MYBB_ROOT . $dm_stylesheet_path);
        }

        $dm_stylesheet_url = $mybb->settings['bburl'] . '/' . $dm_stylesheet_path;

        global $stylesheets;
        $stylesheets .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"{$dm_stylesheet_url}\" />\n";
    }
}

function darkmodeswitch_update_stylesheet_simple()
{
    global $theme, $new_stylesheet, $stylesheet;

    $name = 'darkmode.css';
    if ($stylesheet['name'] != $name)
    {
        return false;
    }

    $tid = $theme['tid'];
    $css = $new_stylesheet;

    darkmodeswitch_create_auto_stylesheet($tid, $css);
}

function darkmodeswitch_update_stylesheet_advanced()
{
    global $theme, $mybb, $stylesheet;

    $name = 'darkmode.css';
    if ($stylesheet['name'] != $name)
    {
        return false;
    }

    $tid = $theme['tid'];
    $css = $mybb->input['stylesheet'];

    darkmodeswitch_create_auto_stylesheet($tid, $css);
}

function darkmodeswitch_create_auto_stylesheet(int $tid, $css = "")
{
    if (empty($css))
    {
        return false;
    }

    if (!$tid)
    {
        $tid = 1;
    }

    global $mybb;
    require_once MYBB_ROOT . $mybb->config['admin_dir'] . '/inc/functions_themes.php';

    $stylesheet = trim($css);
    $stylesheet = explode("\n", $stylesheet);

    $stylesheet_auto = "@media (prefers-color-scheme: dark) {";
    $stylesheet_auto .= "\n";
    foreach ($stylesheet as $line_key => $line_value)
    {
        $stylesheet_auto .= "\t" . $line_value . "\n";
    }
    $stylesheet_auto .= "}";

    cache_stylesheet($tid, 'darkmode_auto.css', $stylesheet_auto);
    unset($stylesheet, $stylesheet_auto);

    return true;
}

function darkmodeswitch_remove_stylesheets(int $tid)
{
    if (!$tid)
    {
        return false;
    }

    $files = glob(MYBB_ROOT . "cache/themes/theme{$tid}/darkmod*.css");

    if (is_array($files) && !empty($files))
    {
        foreach ($files as $file)
        {
            if (@file_exists($file))
            {
                @unlink($file);
            }
        }
    }

    return true;
}
