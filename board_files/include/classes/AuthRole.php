<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class AuthRole extends AuthBase
{
    public $permissions = array();

    function __construct($database, $role_id)
    {
        $this->database = $database;
        $this->auth_id = $role_id;
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT * FROM "' . ROLES_TABLE . '" WHERE "role_id" = ? LIMIT 1');
        $result = $database->executePreparedFetch($prepared, [$this->auth_id], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        $this->auth_data = $result;
        $this->permissions = new \Nelliel\AuthPermissions($database, $this->auth_id);
        $this->permissions->loadFromDatabase();
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->auth_data))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT "entry" FROM "' . ROLES_TABLE . '" WHERE "role_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->auth_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . ROLES_TABLE .
                    '" SET "role_id" = :role_id, "role_level" = :role_level, "role_title" = :role_title, "capcode_text" = :capcode_text WHERE "entry" = :entry');
            $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . ROLES_TABLE . '" ("role_id", "role_level", "role_title", "capcode_text") VALUES
                    (:role_id, :role_level, :role_title, :capcode_text)');
        }

        $prepared->bindValue(':role_id', $this->authDataOrDefault('role_id', $this->auth_id), PDO::PARAM_STR);
        $prepared->bindValue(':role_level', $this->authDataOrDefault('role_level', 0), PDO::PARAM_INT);
        $prepared->bindValue(':role_title', $this->authDataOrDefault('role_title', null), PDO::PARAM_STR);
        $prepared->bindValue(':capcode_text', $this->authDataOrDefault('capcode_text', null), PDO::PARAM_STR);
        $database->executePrepared($prepared);
        $this->permissions->writeToDatabase();
        return true;
    }

    public function checkPermission($permission_id)
    {
        if(isset($this->permissions->auth_data[$permission_id]))
        {
            return $this->permissions->auth_data[$permission_id];
        }

        return false;
    }
}

