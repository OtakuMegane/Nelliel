<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Access point for database connections.
// Databases connection can be added, retrieved or removed using the hash table ID.
//

function nel_database($input = null, $wat_do = null)
{
    static $databases = array();
    static $default_database;

    // No arguments provided: send back the default database
    if (is_null($wat_do) && is_null($input))
    {
        if (!isset($default_database))
        {
            $default_database = nel_default_database_connection();
        }

        return $default_database;
    }

    // ID provided but no instructions: send back the requested database if available
    if (is_null($wat_do) && !is_null($input))
    {
        if (array_key_exists($input, $databases))
        {
            return $databases[$input];
        }
    }

    // Both ID and instructions provided
    if (!is_null($wat_do) && !is_null($input))
    {
        switch ($wat_do)
        {
            case 'store':
                $id = spl_object_hash($input);
                $databases[$id] = $input;
                return $id;
                break;

            case 'retrieve':
                if (array_key_exists($input, $databases))
                {
                    return $databases[$input];
                }

                break;

            case 'identify':
                if (in_array($input, $databases))
                {
                    return array_search($input, $databases);
                }
                break;

            case 'remove':
                if (array_key_exists($input, $databases))
                {
                    unset($input);
                    return true;
                }

                break;
        }
    }

    return false;
}

function nel_authorize()
{
    static $authorize;

    if (!isset($authorize))
    {
        $authorize = new \Nelliel\Authorization();
    }

    return $authorize;
}

// Legacy. TODO: Remove when no longer accessed.
function nel_get_authorization()
{
    return nel_authorize();
}