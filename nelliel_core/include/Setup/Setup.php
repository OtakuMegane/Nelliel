<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
use \Nelliel\SQLCompatibility;

class Setup
{

    function __construct()
    {
    }

    public function install()
    {
        if ($this->checkInstallDone())
        {
            nel_derp(108, _gettext('Installation has already been completed!'));
        }

        $this->checkDBEngine();
        $this->mainDirWritable();
        $this->boardFilesDirWritable();
        $this->configDirWritable();
        $this->checkGenerated();
        $this->createCoreTables();
        $this->createCoreDirectories();
        $site_domain = new \Nelliel\DomainSite(nel_database());
        $regen = new \Nelliel\Regen();
        $regen->siteCache($site_domain);
        //$regen->news($site_domain);
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->writeInternalFile(GENERATED_FILE_PATH . 'install_done.php', '', true, false);
        echo _gettext(
                "Install has finished with no apparent problems! When you're ready to continue, follow this link: ");
        echo '<a href="' . BASE_WEB_PATH . '">' . _gettext('Default home page') . '</a>';
        die();
    }

    public function checkInstallDone()
    {
        return file_exists(GENERATED_FILE_PATH . 'install_done.php');
    }

    public function generateConfigValues()
    {
        $generated = array();
        $generated['tripcode_pepper'] = base64_encode(random_bytes(32));
        $generated['ip_pepper'] = base64_encode(random_bytes(32));
        $generated['poster_id_pepper'] = base64_encode(random_bytes(32));
        $generated['post_password_pepper'] = base64_encode(random_bytes(32));
        return $generated;
    }

    public function checkGenerated()
    {
        if (!file_exists(GENERATED_FILE_PATH . 'generated.php'))
        {
            $file_handler = new \Nelliel\FileHandler();
            $generated = $this->generateConfigValues();
            $prepend = "\n" . '// DO NOT EDIT THESE VALUES OR REMOVE THIS FILE UNLESS YOU HAVE A DAMN GOOD REASON';
            $file_handler->writeInternalFile(GENERATED_FILE_PATH . 'generated.php',
                    $prepend . "\n" . '$generated = ' . var_export($generated, true) . ';', true, false);
        }
    }

    public function boardFilesDirWritable()
    {
        if (!is_writable(NELLIEL_CORE_PATH))
        {
            nel_derp(104, _gettext('Board files directory is missing or not writable. Admin should check this out.'));
        }
    }

    public function mainDirWritable()
    {
        if (!is_writable(BASE_PATH))
        {
            nel_derp(105, _gettext('Nelliel main directory is not writable. Admin should check this out.'));
        }
    }

    public function configDirWritable()
    {
        if (!is_writable(CONFIG_FILE_PATH))
        {
            nel_derp(106, _gettext('Configuration directory is missing or not writable. Admin should check this out.'));
        }
    }

    public function checkDBEngine()
    {
        if ((SQLTYPE === 'MYSQL' || SQLTYPE === 'MARIADB') && !$this->checkForInnoDB())
        {
            nel_derp(102,
                    _gettext(
                            'InnoDB engine is required for MySQL or MariaDB support. However the engine is not available for some reason.'));
        }
    }

    public function checkCore($board_id)
    {
        $this->checkGenerated();
        $this->checkDBEngine();
        $this->createCoreTables();
        $this->createCoreDirectories();
    }

    public function createCoreTables()
    {
        $database = nel_database();
        $sql_compatibility = new SQLCompatibility($database);
        $assets_table = new TableAssets($database, $sql_compatibility);
        $assets_table->setup();
        $bans_table = new TableBans($database, $sql_compatibility);
        $bans_table->setup();
        $board_data_table = new TableBoardData($database, $sql_compatibility);
        $board_data_table->setup();
        $captcha_table = new TableCaptcha($database, $sql_compatibility);
        $captcha_table->setup();
        $cites_table = new TableCites($database, $sql_compatibility);
        $cites_table->setup();
        $board_defaults_table = new TableBoardConfig($database, $sql_compatibility);
        $board_defaults_table->tableName(BOARD_DEFAULTS_TABLE);
        $board_defaults_table->setup();
        $board_defaults_table->insertDefaults();
        $file_filters_table = new TableFileFilters($database, $sql_compatibility);
        $file_filters_table->setup();
        $filetypes_table = new TableFiletypes($database, $sql_compatibility);
        $filetypes_table->setup();
        $login_attempts_table = new TableLoginAttempts($database, $sql_compatibility);
        $login_attempts_table->setup();
        $news_table = new TableNews($database, $sql_compatibility);
        $news_table->setup();
        $permissions_table = new TablePermissions($database, $sql_compatibility);
        $permissions_table->setup();
        $reports_table = new TableReports($database, $sql_compatibility);
        $reports_table->setup();
        $role_permissions_table = new TableRolePermissions($database, $sql_compatibility);
        $role_permissions_table->setup();
        $roles_table = new TableRoles($database, $sql_compatibility);
        $roles_table->setup();
        $site_config_table = new TableSiteConfig($database, $sql_compatibility);
        $site_config_table->setup();
        $staff_logs_table = new TableLogs($database, $sql_compatibility);
        $staff_logs_table->tableName(STAFF_LOGS_TABLE);
        $staff_logs_table->setup();
        $system_logs_table = new TableLogs($database, $sql_compatibility);
        $system_logs_table->tableName(SYSTEM_LOGS_TABLE);
        $system_logs_table->setup();
        $templates_table = new TableTemplates($database, $sql_compatibility);
        $templates_table->setup();
        $user_roles_table = new TableUserRoles($database, $sql_compatibility);
        $user_roles_table->setup();
        $users_table = new TableUsers($database, $sql_compatibility);
        $users_table->setup();
        $versions_table = new TableVersions($database, $sql_compatibility);
        $versions_table->setup();
    }

    public function createCoreDirectories()
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory(CACHE_FILE_PATH, DIRECTORY_PERM, true);
        $file_handler->createDirectory(GENERATED_FILE_PATH, DIRECTORY_PERM, true);
    }

    public function createBoardTables($board_id)
    {
        $database = nel_database();
        $sql_compatibility = new SQLCompatibility($database);

        // Domain and such doesn't function without config table
        $config_table = new TableBoardConfig($database, $sql_compatibility);
        $config_table->tableName('_' . $board_id . '_config');
        $config_table->setup();
        $config_table->copyFrom(BOARD_DEFAULTS_TABLE);

        $domain = new \Nelliel\DomainBoard($board_id, nel_database());
        $references = $domain->reference();
        $threads_table = new TableThreads($database, $sql_compatibility);
        $threads_table->tableName($domain->reference('threads_table'));
        $threads_table->createTable();
        $threads_table->tableName($domain->reference('archive_threads_table'));
        $threads_table->createTable();
        $posts_table = new TablePosts($database, $sql_compatibility);
        $posts_table->tableName($domain->reference('posts_table'));
        $posts_table->createTable(['threads_table' => $domain->reference('threads_table')]);
        $posts_table->tableName($domain->reference('archive_posts_table'));
        $posts_table->createTable(['threads_table' => $domain->reference('archive_threads_table')]);
        $content_table = new TableContent($database, $sql_compatibility);
        $content_table->tableName($domain->reference('content_table'));
        $content_table->createTable(['posts_table' => $domain->reference('posts_table')]);
        $content_table->tableName($domain->reference('archive_content_table'));
        $content_table->createTable(['posts_table' => $domain->reference('archive_posts_table')]);
    }

    public function createBoardDirectories($board_id)
    {
        $file_handler = new \Nelliel\FileHandler();
        $domain = new \Nelliel\DomainBoard($board_id, nel_database());
        $references = $domain->reference();
        $file_handler->createDirectory($references['src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['preview_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['page_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_preview_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_page_path'], DIRECTORY_PERM, true);
    }

    private function checkForInnoDB()
    {
        $database = nel_database();
        $result = $database->query("SHOW ENGINES");
        $list = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as $entry)
        {
            if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES'))
            {
                return true;
            }
        }

        return false;
    }
}