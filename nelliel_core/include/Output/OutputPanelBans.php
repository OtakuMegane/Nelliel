<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelBans extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        if (!isset($parameters['section']))
        {
            return;
        }

        $user = $parameters['user'];

        if (!$user->checkPermission($this->domain, 'perm_manage_bans'))
        {
            nel_derp(320, _gettext('You are not allowed to access the Bans panel.'));
        }

        switch ($parameters['section'])
        {
            case 'panel':
                $output = $this->renderPanel($parameters, $data_only);
                break;

            case 'add':
                $output = $this->renderAdd($parameters, $data_only);
                break;

            case 'modify':
                $output = $this->renderModify($parameters, $data_only);
                break;
        }

        return $output;
    }

    private function renderPanel(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $user = $parameters['user'];
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Bans')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['can_modify'] = $user->checkPermission($this->domain, 'perm_manage_bans');

        if ($this->domain->id() !== '_site_')
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . BANS_TABLE . '" WHERE "board_id" = ? ORDER BY "ban_id" DESC');
            $ban_list = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }
        else
        {
            $ban_list = $this->database->executeFetchAll('SELECT * FROM "' . BANS_TABLE . '" ORDER BY "ban_id" DESC',
                    PDO::FETCH_ASSOC);
        }

        $bgclass = 'row1';

        foreach ($ban_list as $ban_info)
        {
            $ban_data = array();
            $ban_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $ban_data['ban_id'] = $ban_info['ban_id'];
            $ban_data['type'] = $ban_info['type'];
            $ban_data['ip_address_start'] = $ban_info['ip_address_start'] ? @inet_ntop($ban_info['ip_address_start']) : _gettext(
                    'Unknown');
            $ban_data['ip_address_start'] = $ban_data['ip_address_start'];
            $ban_data['board_id'] = $ban_info['board_id'];
            $ban_data['reason'] = $ban_info['reason'];
            $ban_data['expiration'] = date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']);
            $ban_data['appeal'] = $ban_info['appeal'];
            $ban_data['appeal_response'] = $ban_info['appeal_response'];
            $ban_data['appeal_status'] = $ban_info['appeal_status'];
            $ban_data['modify_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=modify&ban_id=' .
                    $ban_info['ban_id'] . '&board_id=' . $this->domain->id();
            $ban_data['remove_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=remove&ban_id=' .
                    $ban_info['ban_id'] . '&board_id=' . $this->domain->id();
            $this->render_data['ban_list'][] = $ban_data;
        }

        $this->render_data['new_ban_url'] = MAIN_SCRIPT . '?module=board&module=bans&action=new&board_id=' .
                $this->domain->id();
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/bans_panel_main',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function renderAdd(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Add Ban')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['ban_board'] = (!empty($this->domain->id())) ? $this->domain->id() : '';
        $ip = $parameters['ip'];
        $type = $parameters['type'];

        if ($type === 'POST' && isset($_GET['post-id']))
        {
            $this->render_data['is_post_ban'] = true;
            $post_param = '&post-id=' . $_GET['post-id'];
        }
        else
        {
            $post_param = '';
        }

        $this->render_data['form_action'] = MAIN_SCRIPT . '?module=board&module=bans&action=add&board_id=' .
                $this->domain->id() . $post_param;
        $this->render_data['ban_ip'] = $ip;
        $this->render_data['ban_type'] = $type;
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/bans_panel_add',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }

    private function renderModify(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Modify Ban')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['form_action'] = MAIN_SCRIPT . '?module=board&module=bans&action=update&board_id=' .
                $this->domain->id();
        $ban_id = $_GET['ban_id'];
        $ban_hammer = new \Nelliel\BanHammer($this->database);
        $ban_info = $ban_hammer->getBanById($ban_id, true);
        $this->render_data['ban_id'] = $ban_info['ban_id'];
        $this->render_data['ip_address_start'] = @inet_ntop($ban_info['ip_address_start']);
        $this->render_data['board_id'] = $ban_info['board_id'];
        $this->render_data['type'] = $ban_info['type'];
        $this->render_data['start_time_formatted'] = date("D F jS Y  H:i:s", $ban_info['start_time']);
        $this->render_data['expiration'] = date("D F jS Y  H:i:s", $ban_info['length'] + $ban_info['start_time']);
        $this->render_data['years'] = $ban_info['years'];
        $this->render_data['days'] = $ban_info['days'];
        $this->render_data['hours'] = $ban_info['hours'];
        $this->render_data['minutes'] = $ban_info['minutes'];
        $this->render_data['all_boards'] = ($ban_info['all_boards'] > 0) ? 'checked' : '';
        $this->render_data['start_time'] = $ban_info['start_time'];
        $this->render_data['reason'] = $ban_info['reason'];
        $this->render_data['creator'] = $ban_info['creator'];
        $this->render_data['appeal'] = $ban_info['appeal'];
        $this->render_data['appeal_response'] = $ban_info['appeal_response'];

        if ($ban_info['appeal_status'] == 0)
        {
            $this->render_data['status_unappealed'] = 'checked';
        }

        if ($ban_info['appeal_status'] == 1)
        {
            $this->render_data['status_appealed'] = 'checked';
        }

        if ($ban_info['appeal_status'] == 2)
        {
            $this->render_data['status_modified'] = 'checked';
        }

        if ($ban_info['appeal_status'] == 3)
        {
            $this->render_data['status_denied'] = 'checked';
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/bans_panel_modify',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}