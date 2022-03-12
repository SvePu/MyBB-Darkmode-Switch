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

if (defined('IN_ADMINCP'))
{
    $plugins->add_hook('admin_config_settings_begin', 'darkmodeswitch_settings');
}
else
{
    $plugins->add_hook('usercp_options_end', 'darkmodeswitch_usercp_options');
    $plugins->add_hook('usercp_do_options_end', 'darkmodeswitch_usercp_do_options');
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
    global $db, $mybb;

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
        'darkmode.css' => "body {
    background: #282828;
    color: #ccc;
}

#container,
.navigation,
.pagination .pagination_current {
    color: #ccc;
}

.navigation img {
    filter: invert(1);
}

#content {
    background: #282828;
}

a:link,
a:visited,
a:active,
.navigation .active {
    color: #6fb3df;
}

a:hover {
    color: #ccc;
}

#logo {
    background: #202020 url(images/colors/black_header.png) top left repeat-x;
    border-bottom: 1px solid #000;
}

#logo .wrapper>a>img {
    content: url(images/logo_white.png);
    filter: brightness(80%);
}

#header ul.menu li a {
    color: #bbb;
}

#panel .upper {
    background: #0f0f0f url(images/tcat.png) repeat-x;
    color: #ccc;
    border-top-color: #444;
    border-bottom-color: #444;
}

#panel .upper a:link,
#panel .upper a:visited,
#panel .upper a:hover,
#panel .upper a:active {
    color: #ccc;
}

#panel input.textbox {
    border-color: #777;
}

#panel input.button {
    background: #202121 url(images/colors/black_thead.png) top left repeat-x;
    color: #ccc;
    border-color: #666;
}

#panel .lower {
    background: #363636;
    color: #bbb;
    border-top-color: #444;
    border-bottom-color: #222;
}

#panel .lower a:link,
#panel .lower a:visited,
#panel .lower a:hover,
#panel .lower a:active {
    color: #bbb;
}

table {
    color: #bbb;
}

.tborder {
    background: #333;
    border-color: #666;
}

.thead {
    background: #202121 url(images/colors/black_thead.png) top left repeat-x;
    border-bottom-color: #222;
    color: #bbb;
}

.thead a:link {
    color: #bbb;
}

.tcat {
    background: #303030;
    color: #ccc;
    border-top-color: #444;
    border-bottom-color: #555;
}

.tcat a:link,
.tcat a:visited,
.tcat a:hover,
.tcat a:active {
    color: #ccc;
}

.thead input.textbox,
.thead select {
    border-color: #777;
}

.trow1 {
    background: #3a3a3a;
    border-color: #2e2e2e #4e4e4e #4e4e4e #2e2e2e;
}

.trow2 {
    background: #3d3d3d;
    border-color: #2e2e2e #4e4e4e #4e4e4e #2e2e2e;
}

.trow_sep {
    background: #523636;
    color: #bbb;
    border-bottom-color: #542e2e;
}

.tfoot {
    border-top-color: #222;
    background: #303030;
    color: #aaa;
}

.tfoot a:link,
.tfoot a:visited {
    color: #bbb;
}

.tfoot a:hover,
.tfoot a:active {
    color: #ddd;
}

.post .post_author {
    border-bottom-color: #181818;
    border-top-color: #333;
    background: #222;
}

.post .post_author div.author_avatar img {
    border-color: #777;
    background: #333;
}

.post .post_author div.author_statistics {
    color: #bbb;
}

.post .post_head {
    border-bottom-color: #666;
}

.post .post_head span.post_date {
    color: #aaa;
}

.post_body {
    color: #ddd;
}

.post_controls {
    background: #282828;
    border-bottom-color: #585858;
}

textarea,
select,
input.textbox {
    background: #363636;
    color: #bfbfbf;
    border-color: #777;
}

fieldset,
fieldset.trow1,
fieldset.trow2 {
    border-color: #777;
}

blockquote,
.codeblock {
    border-color: #777;
    background: #363636;
}

blockquote cite,
.codeblock .title {
    border-bottom-color: #777;
}

blockquote cite>span {
    color: #ccc;
}

.postbit_buttons>a:link,
.postbit_buttons>a:hover,
.postbit_buttons>a:visited,
.postbit_buttons>a:active {
    padding: 3px 6px;
    background: #181f3a;
    border: 1px solid #555;
    color: #bbb;
}

a.button:link,
a.button:hover,
a.button:visited,
a.button:active {
    border-color: #777;
    background: #181f3a;
    color: #bbb;
}

button,
input.button {
    padding: 3px 6px;
    background: #181f3a;
    border-color: #777;
    color: #bbb;
}

.reputation_neutral {
    color: #777;
}

.popup_menu .popup_item:hover {
    background: #333;
    color: #fff;
}

.tt-suggestion.tt-is-under-cursor {
    background-color: #333;
    color: #fff;
}

.pagination a {
    background: none;
    border: none;
}

.pagination a:hover {
    background-color: #444;
    color: #ddd;
}

#footer .upper {
    background: #222;
    border-top-color: #333;
    border-bottom-color: #333;
}

#footer a:link,
#footer a:visited,
#footer a:hover,
#footer a:active {
    color: #999;
}

#footer .upper .language select,
#footer .upper .theme select {

    border-color: #777;
}

#footer .lower,
#footer .lower a:link,
#footer .lower a:visited {
    color: #777;
}

#footer .lower a:hover,
#footer .lower a:active {
    color: #bbb;
}

#footer .lower #current_time {
    color: #999;
}

.select2-container .select2-choice,
.select2-container-multi .select2-choices{
    border-color: #666 !important;
    background-color: #363636 !important;
    background-image: none !important;
    color: #bbb !important;
}

.select2-container-multi .select2-choices .select2-search-field input {
    color: #bbb !important;
}

.select2-container .select2-choice .select2-arrow {
    border-color: #666 !important;
    border-radius: 0 2px 2px 0 !important;
    background: #333 !important;
    background-image: none !important;
}

.select2-dropdown-open .select2-choice {
    -webkit-box-shadow: none !important;
    box-shadow: none !important;
}

.select2-drop {
    background: #333 !important;
    color: #bbb !important;
    border-color: #666 !important;
    border-radius: 0 0 2px 2px;
}

.select2-drop-active {
    border-color: #666 !important;
}

.select2-search input {
    border-color: #666 !important;
    background: #363636 url(jscripts/select2/select2.png) no-repeat 100% -20px !important;
    color: #bbb !important;
    padding: 0px 20px 0px 5px !important;
    border-radius: 2px !important;
}

.select2-results .select2-no-results,
.select2-results .select2-searching,
.select2-results .select2-ajax-error,
.select2-results .select2-selection-limit {
    background: #333 !important;
}

select.rss_forum_select {
    background: #363636;
}",
        'darkmode_auto.css' => "@media (prefers-color-scheme: dark) {
    body {
        background: #282828;
        color: #ccc;
    }

    #container,
    .navigation,
    .pagination .pagination_current {
        color: #ccc;
    }

    .navigation img {
        filter: invert(1);
    }

    #content {
        background: #282828;
    }

    a:link,
    a:visited,
    a:active,
    .navigation .active {
        color: #6fb3df;
    }

    a:hover {
        color: #ccc;
    }

    #logo {
        background: #202020 url(images/colors/black_header.png) top left repeat-x;
        border-bottom: 1px solid #000;
    }

    #logo .wrapper>a>img {
        content: url(images/logo_white.png);
        filter: brightness(80%);
    }

    #header ul.menu li a {
        color: #bbb;
    }

    #panel .upper {
        background: #0f0f0f url(images/tcat.png) repeat-x;
        color: #ccc;
        border-top-color: #444;
        border-bottom-color: #444;
    }

    #panel .upper a:link,
    #panel .upper a:visited,
    #panel .upper a:hover,
    #panel .upper a:active {
        color: #ccc;
    }

    #panel input.textbox {
        border-color: #777;
    }

    #panel input.button {
        background: #202121 url(images/colors/black_thead.png) top left repeat-x;
        color: #ccc;
        border-color: #666;
    }

    #panel .lower {
        background: #363636;
        color: #bbb;
        border-top-color: #444;
        border-bottom-color: #222;
    }

    #panel .lower a:link,
    #panel .lower a:visited,
    #panel .lower a:hover,
    #panel .lower a:active {
        color: #bbb;
    }

    table {
        color: #bbb;
    }

    .tborder {
        background: #333;
        border-color: #666;
    }

    .thead {
        background: #202121 url(images/colors/black_thead.png) top left repeat-x;
        border-bottom-color: #222;
        color: #bbb;
    }

    .thead a:link {
        color: #bbb;
    }

    .tcat {
        background: #303030;
        color: #ccc;
        border-top-color: #444;
        border-bottom-color: #555;
    }

    .tcat a:link,
    .tcat a:visited,
    .tcat a:hover,
    .tcat a:active {
        color: #ccc;
    }

    .thead input.textbox,
    .thead select {
        border-color: #777;
    }

    .trow1 {
        background: #3a3a3a;
        border-color: #2e2e2e #4e4e4e #4e4e4e #2e2e2e;
    }

    .trow2 {
        background: #3d3d3d;
        border-color: #2e2e2e #4e4e4e #4e4e4e #2e2e2e;
    }

    .trow_sep {
        background: #523636;
        color: #bbb;
        border-bottom-color: #542e2e;
    }

    .tfoot {
        border-top-color: #222;
        background: #303030;
        color: #aaa;
    }

    .tfoot a:link,
    .tfoot a:visited {
        color: #bbb;
    }

    .tfoot a:hover,
    .tfoot a:active {
        color: #ddd;
    }

    .post .post_author {
        border-bottom-color: #181818;
        border-top-color: #333;
        background: #222;
    }

    .post .post_author div.author_avatar img {
        border-color: #777;
        background: #333;
    }

    .post .post_author div.author_statistics {
        color: #bbb;
    }

    .post .post_head {
        border-bottom-color: #666;
    }

    .post .post_head span.post_date {
        color: #aaa;
    }

    .post_body {
        color: #ddd;
    }

    .post_controls {
        background: #282828;
        border-bottom-color: #585858;
    }

    textarea,
    select,
    input.textbox {
        background: #363636;
        color: #bfbfbf;
        border-color: #777;
    }

    fieldset,
    fieldset.trow1,
    fieldset.trow2 {
        border-color: #777;
    }

    blockquote,
    .codeblock {
        border-color: #777;
        background: #363636;
    }

    blockquote cite,
    .codeblock .title {
        border-bottom-color: #777;
    }

    blockquote cite>span {
        color: #ccc;
    }

    .postbit_buttons>a:link,
    .postbit_buttons>a:hover,
    .postbit_buttons>a:visited,
    .postbit_buttons>a:active {
        padding: 3px 6px;
        background: #181f3a;
        border: 1px solid #555;
        color: #bbb;
    }

    a.button:link,
    a.button:hover,
    a.button:visited,
    a.button:active {
        border-color: #777;
        background: #181f3a;
        color: #bbb;
    }

    button,
    input.button {
        padding: 3px 6px;
        background: #181f3a;
        border-color: #777;
        color: #bbb;
    }

    .reputation_neutral {
        color: #777;
    }

    .popup_menu .popup_item:hover {
        background: #333;
        color: #fff;
    }

    .tt-suggestion.tt-is-under-cursor {
        background-color: #333;
        color: #fff;
    }

    .pagination a {
        background: none;
        border: none;
    }

    .pagination a:hover {
        background-color: #444;
        color: #ddd;
    }

    #footer .upper {
        background: #222;
        border-top-color: #333;
        border-bottom-color: #333;
    }

    #footer a:link,
    #footer a:visited,
    #footer a:hover,
    #footer a:active {
        color: #999;
    }

    #footer .upper .language select,
    #footer .upper .theme select {

        border-color: #777;
    }

    #footer .lower,
    #footer .lower a:link,
    #footer .lower a:visited {
        color: #777;
    }

    #footer .lower a:hover,
    #footer .lower a:active {
        color: #bbb;
    }

    #footer .lower #current_time {
        color: #999;
    }

    .select2-container .select2-choice,
    .select2-container-multi .select2-choices{
        border-color: #666 !important;
        background-color: #363636 !important;
        background-image: none !important;
        color: #bbb !important;
    }

    .select2-container-multi .select2-choices .select2-search-field input {
        color: #bbb !important;
    }

    .select2-container .select2-choice .select2-arrow {
        border-color: #666 !important;
        border-radius: 0 2px 2px 0 !important;
        background: #333 !important;
        background-image: none !important;
    }

    .select2-dropdown-open .select2-choice {
        -webkit-box-shadow: none !important;
        box-shadow: none !important;
    }

    .select2-drop {
        background: #333 !important;
        color: #bbb !important;
        border-color: #666 !important;
        border-radius: 0 0 2px 2px;
    }

    .select2-drop-active {
        border-color: #666 !important;
    }

    .select2-search input {
        border-color: #666 !important;
        background: #363636 url(jscripts/select2/select2.png) no-repeat 100% -20px !important;
        color: #bbb !important;
        padding: 0px 20px 0px 5px !important;
        border-radius: 2px !important;
    }

    .select2-results .select2-no-results,
    .select2-results .select2-searching,
    .select2-results .select2-ajax-error,
    .select2-results .select2-selection-limit {
        background: #333 !important;
    }

    select.rss_forum_select {
        background: #363636;
    }
}"
    );

    foreach ($stylesheetarray as $name => $styles)
    {
        $query = $db->simple_select('themestylesheets', 'sid', "name='{$name}' AND tid='{$tid}'");
        if ($db->fetch_field($query, 'sid'))
        {
            continue;
        }

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

        update_theme_stylesheet_list($tid, false, true);
    }

    /** Add Settings */
    $query = $db->simple_select('settinggroups', 'gid', "name='general'");
    $gid = (int)$db->fetch_field($query, 'gid');

    $query = $db->simple_select('settings', 'COUNT(*) AS disporder', "gid='{$gid}'");
    $disporder = (int)$db->fetch_field($query, 'disporder');

    $settings = array(
        'darkmodeselector' => array(
            'optionscode' => 'yesno',
            'value' => 1
        ),
        'autodarkmodeguests' => array(
            'optionscode' => 'yesno',
            'value' => 1,
        )
    );

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

    $db->delete_query('templates', "title IN ('usercp_options_darkmodeswitch')");

    $db->delete_query("settings", "name IN ('darkmodeselector', 'autodarkmodeguests')");
    rebuild_settings();

    if (!isset($mybb->input['no']))
    {
        /** Remove Stylesheet */
        require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

        $db->delete_query("themestylesheets", "name LIKE ('darkmode%')");

        $query = $db->simple_select("themes", "tid");
        while ($theme = $db->fetch_array($query))
        {
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
    find_replace_templatesets('codebuttons', '#' . preg_quote('<script type="text/javascript">') . '#', "{\$theme['iconsscript']}\n<script type=\"text/javascript\">");
    find_replace_templatesets('codebuttons', '#' . preg_quote('format: "bbcode",') . '#', "format: \"bbcode\",{\$theme['icons']}");
}

function darkmodeswitch_deactivate()
{
    require MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('usercp_options', '#' . preg_quote("{\$board_darkmode}\n") . '#', '');
    find_replace_templatesets('codebuttons', '#' . preg_quote("{\$theme['iconsscript']}\n") . '#', '');
    find_replace_templatesets('codebuttons', '#' . preg_quote("{\$theme['icons']}") . '#', '');
}

function darkmodeswitch_settings()
{
    global $lang;
    $lang->load('darkmodeswitch', true);
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
    global $db, $mybb, $user, $config, $theme;

    $update_array = array(
        'darkmode' => $mybb->get_input('darkmode', MyBB::INPUT_INT)
    );

    $db->update_query("users", $update_array, "uid = '" . $user['uid'] . "'");
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
