<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function admin_dispatch($dataforce)
{
    $authorize = nel_authorize();
    nel_verify_login_or_session($dataforce);

    switch ($dataforce['mode_segments'][1])
    {
        case 'staff':
            require_once INCLUDE_PATH . 'admin/staff_panel.php';
            nel_staff_panel($dataforce);
            break;

        case 'ban':
            require_once INCLUDE_PATH . 'admin/bans_panel.php';
            nel_ban_control($dataforce);
            break;

        case 'modmode':
            nel_thread_updates($dataforce, INPUT_BOARD_ID);
            echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF . '?mode=display&page=0">';
            break;

        case 'settings':
            require_once INCLUDE_PATH . 'admin/settings_panel.php';
            nel_settings_control($dataforce);
            break;

        case 'regen':
            if ($dataforce['mode_segments'][2] === 'full')
            {
                nel_regen_all_pages($dataforce, INPUT_BOARD_ID);
            }

            if ($dataforce['mode_segments'][2] === 'index')
            {
                nel_regen_index($dataforce, INPUT_BOARD_ID);
            }

            if ($dataforce['mode_segments'][2] === 'thread')
            {
                nel_regen_threads($dataforce, INPUT_BOARD_ID, true, null);
            }

            if ($dataforce['mode_segments'][2] === 'cache')
            {
                nel_regen_cache($dataforce);
            }

            nel_login($dataforce);
            break;

        case 'thread':
            require_once INCLUDE_PATH . 'admin/threads_panel.php';
            nel_thread_panel($dataforce, $authorize);
            break;

        case 'login':
            nel_login($dataforce);
            break;

        case 'selectboard':
            nel_login($dataforce);
            break;

        case 'board':
            require_once INCLUDE_PATH . 'admin/create_board.php';
            nel_create_new_board();
            nel_regen_all_pages($dataforce);
            nel_regen_cache($dataforce);
            nel_login($dataforce);
            break;

        case 'createboard':
            require_once INCLUDE_PATH . 'output/management/create_board.php';
            nel_generate_create_board_panel();
            break;

        default:
            nel_derp(400, nel_stext('ERROR_400'));
    }

    nel_clean_exit($dataforce, TRUE);
}
