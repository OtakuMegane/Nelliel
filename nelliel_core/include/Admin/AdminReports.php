<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminReports extends Admin
{
    private $defaults = false;

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelReports($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        $report_id = $_GET['report_id'];

        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_reports'))
        {
            nel_derp(383, _gettext('You are not allowed to dismiss reports.'));
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_REPORTS_TABLE . '" WHERE "report_id" = ?');
        $this->database->executePrepared($prepared, [$report_id]);
        $this->outputMain(true);
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_reports'))
        {
            nel_derp(380, _gettext('You are not allowed to access the reports panel.'));
        }
    }
}
