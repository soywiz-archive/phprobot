<?php

/**
 * HYPERLIB - A generic PHP funtion library
 *
 * Copyright © 2004-2005 Hypervisual
 * pechansky@hypervisual.com
 *
 * Database wrapper functions for WinBinder (MySQL-specific)
 */
// TODO: Not tested yet
// -------------------------------------------------------------------- LIBRARIES
/*if(!extension_loaded('mysql'))
	dl('php_mysql.dll');			// For PHP 5
*/
// -------------------------------------------------------------------- CONSTANTS
define("FETCH_BOTH", MYSQL_BOTH);
define("FETCH_NUM", MYSQL_NUM);
define("FETCH_ASSOC", MYSQL_ASSOC);
// ----------------------------------------------------------- DATABASE FUNCTIONS
/* Opens and connects a database. Create the database if it does not exist. */

function db_open_database($database, $server = "", $username = "", $password = "")
{
    /**
     * if (!mysql_query("CREATE DATABASE IF NOT EXISTS " . $database))
     * die(mysql_error);
     *
     * //
     */
    /**
     * bool mysql_select_db ( string database_name [, resource link_identifier]);
     * //
     */

    /**
     * if (!mysql_select_db($database))
     * die(mysql_error);
     *
     * //
     *//**
     * resource mysql_connect ( [string Server [, string Benutzername
     * [, string Benutzerkennwort [, bool neue_Verbindung [, int client_flags]]]]])
     */
    $conn = mysql_connect($server, $username, $password);
    if (!$conn)
        trigger_error(__FUNCTION__ . mysql_error);
    else {
        /**
         * bool mysql_select_db ( string database_name [, resource link_identifier]);
         * //
         */
        if (!mysql_select_db($database))
            trigger_error(__FUNCTION__ . mysql_error);
    }
    return $conn;
}

function db_close_database()
{
    return mysql_close();
}

/* Returns an array with the list of tables of the current database. */

function db_list_database_tables($database)
{
    /**
     * resource mysql_list_tables ( string database [, resource link_identifier]);
     * //
     */

    $hresult = mysql_list_tables($database);
    if (!$hresult) {
        // no Tables in $database
    } else {
        /**
         * array mysql_fetch_row ( resource result)	one row only
         * //
         */
        $tables = array();
        while ($row = mysql_fetch_array($hresult, MYSQL_NUM)) {
            $tables[] = $row[0];
        } // while
        return $tables;
    }
}
// ---------------------------------------------------------------- SQL FUNCTIONS
function db_query($query)
{
    return mysql_query($query);
}

function db_fetch_array($result, $type = FETCH_BOTH)
{
    /**
     * array mysql_fetch_array ( resource result [, int result_type])
     * int type  MYSQL_ASSOC, MYSQL_NUM ( == fetch_row), and MYSQL_BOTH
     * //
     */

    return mysql_fetch_array($result, $type);
}

function db_free_result($result)
{
    mysql_free_result($result);
}

function db_escape_string($str)
{
    /**
     * string mysql_real_escape_string ( string unescaped_string [, resource link_identifier])
     * //
     */

    return mysql_real_escape_string($str);
}
// -------------------------------------------------------------- TABLE FUNCTIONS
function db_rename_table($tablename, $newname)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;

    /**
     * resource mysql_query ( string query [, resource link_identifier])
     *
     * //
     */

    mysql_query("RENAME TABLE $tablename TO $newname");
    $g_lasttable = $newname;
}
// -------------------------------------------------------------- FIELD FUNCTIONS
function db_delete_field($tablename, $field)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    mysql_query("ALTER TABLE $tablename DROP $field");
}

function db_create_field($tablename, $field, $type)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    mysql_query("ALTER TABLE $tablename ADD $field $type");
}

function db_edit_field($tablename, $field, $type)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    mysql_query("ALTER TABLE $tablename MODIFY $field $type");
}

function db_rename_field($tablename, $field, $newname, $type)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    mysql_query("ALTER TABLE $tablename CHANGE $field $newname $type");
}
// ------------------------------------------------------------------ END OF FILE

?>
