<?php

namespace Nelliel\Admin;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/board_settings_panel.php';

class AdminBoardSettings extends AdminHandler
{
    private $domain;
    private $defaults = false;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->defaults = ($this->domain->id() === '') ? true : false;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'update')
        {
            $this->update($user);
            $this->renderPanel($user);
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_board_config_access'))
        {
            nel_derp(330, _gettext('You are not allowed to access the board settings panel.'));
        }

        if ($this->defaults && !$user->boardPerm('', 'perm_board_defaults_access'))
        {
            nel_derp(332, _gettext('You are not allowed to access the default board settings panel.'));
        }

        nel_render_board_settings_panel($user, $this->domain, $this->defaults);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
        if (!$user->boardPerm($this->domain->id(), 'perm_board_config_modify'))
        {
            nel_derp(331, _gettext('You are not allowed to modify the board settings.'));
        }

        if ($this->defaults && !$user->boardPerm('', 'perm_board_defaults_modify'))
        {
            nel_derp(333, _gettext('You are not allowed to modify the default board settings.'));
        }

        $config_table = ($this->defaults) ? BOARD_DEFAULTS_TABLE : $this->domain->reference('config_table');
        $lock_override = $user->boardPerm($this->domain->id(), 'perm_board_config_lock_override');

        while ($item = each($_POST))
        {
            if ($item[0] === 'jpeg_quality' && $item[1] > 100)
            {
                $item[0] = 100;
            }

            if (substr($item[0], -5) === '_lock' && $this->defaults)
            {
                $config_name = substr($item[0], 0, strlen($item[0]) - 5);
                $this->setLock($config_table, $config_name, $item[1]);

                foreach ($this->getBoardDomains() as $board_domain)
                {
                    $this->setLock($board_domain->reference('config_table'), $config_name, $item[1]);
                }
            }
            else
            {
                $this->updateSetting($config_table, $item[0], $item[1], $lock_override);
            }
        }

        if (!$this->defaults)
        {
            $regen = new \Nelliel\Regen();
            $regen->boardCache($this->domain);
            $regen->allPages($this->domain);
        }
    }

    public function remove($user)
    {
    }

    private function setLock($config_table, $config_name, $setting)
    {
        $prepared = $this->database->prepare(
                'UPDATE "' . $config_table . '" SET "edit_lock" = ? WHERE "config_name" = ?');
        $this->database->executePrepared($prepared, [$setting, $config_name], true);
    }

    private function updateSetting($config_table, $config_name, $setting, $lock_override)
    {
        if ($this->defaults || $lock_override)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $config_table . '" SET "setting" = ? WHERE "config_name" = ?');
            $this->database->executePrepared($prepared, [$setting, $config_name], true);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $config_table . '" SET "setting" = ? WHERE "config_name" = ? AND "edit_lock" = 0');
            $this->database->executePrepared($prepared, [$setting, $config_name], true);
        }
    }

    private function getBoardDomains()
    {
        $query = 'SELECT "board_id" FROM "' . BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        $board_domains = array();

        foreach ($board_ids as $board_id)
        {
            $board_domains[] = new \Nelliel\Domain($board_id, new \Nelliel\CacheHandler(), $this->database);
        }

        return $board_domains;
    }
}
