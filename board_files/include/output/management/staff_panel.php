<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_staff_panel_main($dataforce)
{
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_main.html');
    $dom->getElementById('board_id_field')->extSetAttribute('value', INPUT_BOARD_ID);
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}

function nel_render_staff_panel_user_edit($dataforce, $user_id)
{
    $dbh = nel_database();
    $authorize = nel_authorize();
    $user = $authorize->get_user($user_id);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_user_edit.html');
    $dom->getElementById('board_id_field')->extSetAttribute('value', INPUT_BOARD_ID);
    $dom->getElementById('user-id-field')->extSetAttribute('value', $user['user_id']);
    $dom->getElementById('user-title-field')->extSetAttribute('value', $user['user_title']);
    $board_roles = $dom->getElementById('board-roles');

    $boards = $dbh->executeFetchAll('SELECT "board_id" FROM "' . BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

    if ($boards !== false)
    {
        foreach ($boards as $board)
        {
            $new_board = $board_roles->cloneNode(true);
            $board_roles->parentNode->appendChild($new_board);
            $new_board->removeAttribute('id');
            $role_board_id_label = $new_board->getElementById('role-board-id-label-');
            $role_board_id_label->setContent($board);
            $role_board_id_label->changeId('role-board-id-label-' . $board);
            $new_board->getElementById('role-board-id-')->changeId('role-board-id-' . $board);
            $new_board->getElementById('all-boards1-')->changeId('all-boards1-' . $board);
            $new_board->getElementById('all-boards2-')->changeId('all-boards2-' . $board);
        }
    }

    $prepared = $dbh->prepare('SELECT * FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ?');
    $user_boards = $dbh->executePreparedFetchAll($prepared, array($_SESSION['username']), PDO::FETCH_ASSOC);

    if ($user_boards !== false)
    {
        foreach ($user_boards as $board_role)
        {
            $board_id_element = $dom->getElementById('role-board-id-' . $board_role['board']);
            $board_id_element->extSetAttribute('name', 'user_board_role_' . $board_role['board']);
            $board_id_element->extSetAttribute('value', $board_role['role_id']);
            $dom->getElementById('all-boards1-' . $board_role['board'])->extSetAttribute('name', 'all_boards_' .
                 $board_role['board']);
            $board_id_element2 = $dom->getElementById('all-boards2-' . $board_role['board']);
            $board_id_element2->extSetAttribute('name', 'all_boards_' . $board_role['board']);

            if ($board_role['all_boards'] == 1)
            {
                $board_id_element2->extSetAttribute('checked', "true");
            }
        }
    }

    $board_roles->removeSelf();
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}

function nel_render_staff_panel_role_edit($dataforce, $role_id)
{
    $authorize = nel_authorize();
    $role = $authorize->get_role($role_id);
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header($dataforce, $render, array());
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/staff_panel_role_edit.html');
    $dom->getElementById('board_id_field')->extSetAttribute('value', INPUT_BOARD_ID);
    $dom->getElementById('role_id')->extSetAttribute('value', $role['role_id']);
    $dom->getElementById('role_level')->extSetAttribute('value', $role['role_level']);
    $dom->getElementById('role_title')->extSetAttribute('value', $role['role_title']);
    $dom->getElementById('capcode_text')->extSetAttribute('value', $role['capcode_text']);

    foreach ($role['permissions'] as $key => $value)
    {
        if ($value === true)
        {
            $dom->getElementById($key)->extSetAttribute('checked', $value);
        }
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_footer($render, false);
    echo $render->outputRenderSet();
}