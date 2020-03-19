<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminTemplates extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function actionDispatch($inputs)
    {
        if ($inputs['action'] === 'add')
        {
            $this->add();
        }
        else if ($inputs['action'] == 'remove')
        {
            $this->remove();
        }
        else if ($inputs['action'] == 'make-default')
        {
            $this->makeDefault();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelTemplates($this->domain);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(421, _gettext('You are not allowed to install templates.'));
        }

        $template_id = $_GET['template-id'];
        $front_end_data = new \Nelliel\FrontEndData($this->database);
        $template_inis = $front_end_data->getTemplateInis();
        $info = '';

        foreach ($template_inis as $ini)
        {
            if ($ini['id'] === $template_id)
            {
                $info = json_encode($ini);
            }
        }

        if ($info !== '')
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . TEMPLATES_TABLE . '" ("id", "type", "is_default", "info") VALUES (?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$template_id, 'template', 0, $info]);
        }

        $this->renderPanel();
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(422, _gettext('You are not allowed to uninstall templates.'));
        }

        $template_id = $_GET['template-id'];
        $prepared = $this->database->prepare(
                'DELETE FROM "' . TEMPLATES_TABLE . '" WHERE "id" = ? AND "type" = \'template\'');
        $this->database->executePrepared($prepared, [$template_id]);
        $this->renderPanel();
    }

    public function makeDefault()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(423, _gettext('You are not allowed to set the default template.'));
        }

        $template_id = $_GET['template-id'];
        $this->database->exec('UPDATE "' . TEMPLATES_TABLE . '" SET "is_default" = 0');
        $prepared = $this->database->prepare('UPDATE "' . TEMPLATES_TABLE . '" SET "is_default" = 1 WHERE "id" = ?');
        $this->database->executePrepared($prepared, [$template_id]);
        $this->renderPanel();
    }
}