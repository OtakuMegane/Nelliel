<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminPermissions extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelPermissions($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_permissions_modify'))
        {
            nel_derp(451, _gettext('You are not allowed to add permissions.'));
        }

        $permission = $_POST['permission'];
        $description = $_POST['description'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_PERMISSIONS_TABLE . '" ("permission", "description") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$permission, $description]);
        $this->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_permissions_modify'))
        {
            nel_derp(453, _gettext('You are not allowed to remove permissions.'));
        }

        $permission = $_GET['permission'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_PERMISSIONS_TABLE . '" WHERE "permission" = ?');
        $this->database->executePrepared($prepared, [$permission]);
        $this->outputMain(true);
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_permissions_access'))
        {
            nel_derp(450, _gettext('You are not allowed to access the permissions panel.'));
        }
    }
}
