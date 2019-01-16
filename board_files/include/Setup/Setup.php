<?php

namespace Nelliel\Setup;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Setup
{

    function __construct()
    {
    }

    public function generateConfigValues($current_values = null)
    {
        $generated = $generated ?? array();
        $generated['tripcode_pepper'] = $generated['tripcode_pepper'] ?? base64_encode(random_bytes(32));
        return $generated;
    }

    public function checkGenerated()
    {
        if (!file_exists(CONFIG_FILE_PATH . 'generated.php'))
        {
            $file_handler = new \Nelliel\FileHandler();
            $generated = $this->generateConfigValues();
            $prepend = "\n" . '// DO NOT EDIT THESE VALUES OR REMOVE THIS FILE UNLESS YOU HAVE A DAMN GOOD REASON';
            $file_handler->writeInternalFile(CONFIG_FILE_PATH . 'generated.php',
                    $prepend . "\n" . '$generated = ' . var_export($generated, true) . ';');
        }
    }

    public function checkAll($board_id)
    {
        $this->checkGenerated();

        if ((SQLTYPE === 'MYSQL' || SQLTYPE === 'MARIADB') && !$this->checkForInnoDB())
        {
            nel_derp(102,
                    _gettext(
                            'InnoDB engine is required for MySQL or MariaDB support. However the engine is not available for some reason.'));
        }

        $this->createCoreTables();

        if (!is_writable(FILES_PATH))
        {
            nel_derp(104, _gettext('Board files directory is missing or not writable. Admin should check this out.'));
        }

        $this->createCoreDirectories();

        if ($board_id !== '')
        {
            $this->createBoardTables($board_id);
            $this->createBoardDirectories($board_id);
        }
    }

    public function createCoreTables()
    {
        $database = nel_database();
        $sql_helpers = new SQLHelpers($database);
        $assets_table = new TableAssets($database, $sql_helpers);
        $assets_table->setup();
        $bans_table = new TableBans($database, $sql_helpers);
        $bans_table->setup();
        $board_data_table = new TableBoardData($database, $sql_helpers);
        $board_data_table->setup();
        $captcha_table = new TableCaptcha($database, $sql_helpers);
        $captcha_table->setup();
        $board_defaults_table = new TableBoardConfig($database, $sql_helpers);
        $board_defaults_table->tableName(BOARD_DEFAULTS_TABLE);
        $board_defaults_table->setup();
        $board_defaults_table->insertDefaults();
        $file_filters_table = new TableFileFilters($database, $sql_helpers);
        $file_filters_table->setup();
        $filetypes_table = new TableFiletypes($database, $sql_helpers);
        $filetypes_table->setup();
        $login_attempts_table = new TableLoginAttempts($database, $sql_helpers);
        $login_attempts_table->setup();
        $permissions_table = new TablePermissions($database, $sql_helpers);
        $permissions_table->setup();
        $reports_table = new TableReports($database, $sql_helpers);
        $reports_table->setup();
        $role_permissions_table = new TableRolePermissions($database, $sql_helpers);
        $role_permissions_table->setup();
        $roles_table = new TableRoles($database, $sql_helpers);
        $roles_table->setup();
        $site_config_table = new TableSiteConfig($database, $sql_helpers);
        $site_config_table->setup();
        $templates_table = new TableTemplates($database, $sql_helpers);
        $templates_table->setup();
        $user_roles_table = new TableUserRoles($database, $sql_helpers);
        $user_roles_table->setup();
        $users_table = new TableUsers($database, $sql_helpers);
        $users_table->setup();
        $versions_table = new TableVersions($database, $sql_helpers);
        $versions_table->setup();
    }

    public function createCoreDirectories()
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory(CACHE_FILE_PATH, DIRECTORY_PERM, true);
    }

    public function createBoardTables($board_id)
    {
        $database = nel_database();
        $sql_helpers = new SQLHelpers($database);
        $board_references = nel_parameters_and_data()->boardReferences($board_id);
        $threads_table = new TableThreads($database, $sql_helpers);
        $threads_table->tableName($board_references['threads_table']);
        $threads_table->createTable();
        $threads_table->tableName($board_references['archive_threads_table']);
        $threads_table->createTable();
        $posts_table = new TablePosts($database, $sql_helpers);
        $posts_table->tableName($board_references['posts_table']);
        $posts_table->createTable(['threads_table' => $board_references['threads_table']]);
        $posts_table->tableName($board_references['archive_posts_table']);
        $posts_table->createTable(['threads_table' => $board_references['archive_threads_table']]);
        $content_table = new TableContent($database, $sql_helpers);
        $content_table->tableName($board_references['content_table']);
        $content_table->createTable(['posts_table' => $board_references['posts_table']]);
        $content_table->tableName($board_references['archive_content_table']);
        $content_table->createTable(['posts_table' => $board_references['archive_posts_table']]);
        $content_table = new TableBoardConfig($database, $sql_helpers);
        $content_table->tableName($board_references['config_table']);
        $content_table->setup();
        $content_table->copyFrom(BOARD_DEFAULTS_TABLE);
    }

    public function createBoardDirectories($board_id)
    {
        $file_handler = new \Nelliel\FileHandler();

        if (!is_writable(BASE_PATH))
        {
            nel_derp(105, _gettext('Nelliel main directory is not writable. Admin should check this out.'));
        }

        $references = nel_parameters_and_data()->boardReferences($board_id);
        $file_handler->createDirectory($references['src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['thumb_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['page_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_thumb_path'], DIRECTORY_PERM, true);
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