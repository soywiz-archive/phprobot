<?php

/**
 * WINBINDER - A native Windows binding for PHP
 *
 * Copyright © 2004-2005 Hypervisual - see LICENSE.TXT for details
 * Author: Rubem Pechansky (pechansky@hypervisual.com)
 *
 * Database wrapper functions for WinBinder (SQLite-specific)
 */
// -------------------------------------------------------------------- LIBRARIES
if (!extension_loaded('sqlite'))
    dl('php_sqlite.dll'); // For PHP 4

// -------------------------------------------------------------------- CONSTANTS
define("FETCH_BOTH", SQLITE_BOTH);
define("FETCH_NUM", SQLITE_NUM);
define("FETCH_ASSOC", SQLITE_ASSOC);
// ----------------------------------------------------------- DATABASE FUNCTIONS
/* Opens and connects a database. $database can be a complete or partial file name.
Create the database if it does not exist. */

function db_open_database($database, $path = "")
{
    global $g_current_db;

    if (!$path) {
        $path = pathinfo(__FILE__);
        $path = $path["dirname"] . "/";
    }

    if (!file_exists($database))

        $database = $path . "sqlite_" . $database . ".db";

    $g_current_db = sqlite_open($database, 0666, $sql_error);
    if (!$g_current_db)
        trigger_error(__FUNCTION__ . $sql_error);
    else
        return $g_current_db;
}

function db_close_database()
{
    global $g_current_db;

    sqlite_close($g_current_db);
    return TRUE;
}

/* Returns an array with the list of tables of the current database.
	See SQLite FAQ and comments in http://hu.php.net/sqlite */

function db_list_database_tables()
{
    global $g_current_db;

    $tables = array();
    $sql = "SELECT name FROM sqlite_master WHERE (type = 'table')";
    $res = sqlite_query($g_current_db, $sql);
    if ($res) {
        while (sqlite_has_more($res)) {
            $tables[] = sqlite_fetch_single($res);
        }
    }
    return $tables;
}
// ---------------------------------------------------------------- SQL FUNCTIONS
function db_query($query)
{
    global $g_current_db;

    return @sqlite_query($g_current_db, $query);
}

function db_fetch_array($result, $type = SQLITE_BOTH)
{
    return sqlite_fetch_array($result, $type);
}

function db_free_result($result)
{
    // Not required in SQLite
}

function db_escape_string($str)
{
    return sqlite_escape_string($str);
}
// -------------------------------------------------------------- TABLE FUNCTIONS
function db_rename_table($tablename, $newname)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;

    __alter_table($tablename, "rename $tablename $newname");
    $g_lasttable = $newname;
}
// -------------------------------------------------------------- FIELD FUNCTIONS
function db_delete_field($tablename, $field)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    __alter_table($tablename, "DROP $field");
}

function db_create_field($tablename, $field, $type)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    __alter_table($tablename, "ADD $field $type");
}

function db_edit_field($tablename, $field, $type)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    __alter_table($tablename, "CHANGE $field $field $type");
}

function db_rename_field($tablename, $field, $newname, $type)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    __alter_table($tablename, "CHANGE $field $newname $type");
}
// ------------------------------------------------------------ PRIVATE FUNCTIONS
/* This function implements a subset of commands from ALTER TABLE.

Adapted from http://code.jenseng.com/db/

*/

function __alter_table($table, $alterdefs)
{
    global $g_current_db;

    $sql = "SELECT sql,name,type FROM sqlite_master WHERE tbl_name = '" . $table . "' ORDER BY type DESC";
    $result = sqlite_query($g_current_db, $sql);

    if (sqlite_num_rows($result) <= 0) {
        trigger_error('no such table: ' . $table, E_USER_WARNING);
        return false;
    }
    // ------------------------------------- Build the queries
    $row = sqlite_fetch_array($result);
    $tmpname = 't' . time();
    $origsql = trim(preg_replace("/[\s]+/", " ", str_replace(",", ", ", preg_replace("/[\(]/", "( ", $row['sql'], 1))));
    $createtemptableSQL = 'CREATE TEMPORARY ' . substr(trim(preg_replace("'" . $table . "'", $tmpname, $origsql, 1)), 6);
    $createindexsql = array();
    $i = 0;
    $defs = preg_split("/[,]+/", $alterdefs, -1, PREG_SPLIT_NO_EMPTY);
    $prevword = $table;
    $oldcols = preg_split("/[,]+/", substr(trim($createtemptableSQL), strpos(trim($createtemptableSQL), '(') + 1), -1, PREG_SPLIT_NO_EMPTY);
    $newcols = array();

    for($i = 0;$i < sizeof($oldcols);$i++) {
        $colparts = preg_split("/[\s]+/", $oldcols[$i], -1, PREG_SPLIT_NO_EMPTY);
        $oldcols[$i] = $colparts[0];
        $newcols[$colparts[0]] = $colparts[0];
    }

    $newcolumns = '';
    $oldcolumns = '';
    reset($newcols);

    while (list($key, $val) = each($newcols)) {
        $newcolumns .= ($newcolumns?', ':'') . $val;
        $oldcolumns .= ($oldcolumns?', ':'') . $key;
    }

    $copytotempsql = 'INSERT INTO ' . $tmpname . '(' . $newcolumns . ') SELECT ' . $oldcolumns . ' FROM ' . $table;
    $dropoldsql = 'DROP TABLE ' . $table;
    $createtesttableSQL = $createtemptableSQL;

    $newname = "";

    foreach($defs as $def) {
        $defparts = preg_split("/[\s]+/", $def, -1, PREG_SPLIT_NO_EMPTY);
        $action = strtolower($defparts[0]);

        switch ($action) {
            case 'add':

                if (sizeof($defparts) <= 2) {
                    trigger_error('near "' . $defparts[0] . ($defparts[1]?' ' . $defparts[1]:'') . '": SQLITE syntax error', E_USER_WARNING);
                    return false;
                }
                $createtesttableSQL = substr($createtesttableSQL, 0, strlen($createtesttableSQL)-1) . ',';
                for($i = 1;$i < sizeof($defparts);$i++)
                $createtesttableSQL .= ' ' . $defparts[$i];
                $createtesttableSQL .= ')';
                break;

            case 'change':

                if (sizeof($defparts) <= 2) {
                    trigger_error('near "' . $defparts[0] . ($defparts[1]?' ' . $defparts[1]:'') . ($defparts[2]?' ' . $defparts[2]:'') . '": SQLITE syntax error', E_USER_WARNING);
                    return false;
                }
                if ($severpos = strpos($createtesttableSQL, ' ' . $defparts[1] . ' ')) {
                    if ($newcols[$defparts[1]] != $defparts[1]) {
                        trigger_error('unknown column "' . $defparts[1] . '" in "' . $table . '"', E_USER_WARNING);
                        return false;
                    }
                    $newcols[$defparts[1]] = $defparts[2];
                    $nextcommapos = strpos($createtesttableSQL, ',', $severpos);
                    $insertval = '';
                    for($i = 2;$i < sizeof($defparts);$i++)
                    $insertval .= ' ' . $defparts[$i];
                    if ($nextcommapos)
                        $createtesttableSQL = substr($createtesttableSQL, 0, $severpos) . $insertval . substr($createtesttableSQL, $nextcommapos);
                    else
                        $createtesttableSQL = substr($createtesttableSQL, 0, $severpos - (strpos($createtesttableSQL, ',')?0:1)) . $insertval . ')';
                } else {
                    trigger_error('unknown column "' . $defparts[1] . '" in "' . $table . '"', E_USER_WARNING);
                    return false;
                }
                break;

            case 'drop';

                if (sizeof($defparts) < 2) {
                    trigger_error('near "' . $defparts[0] . ($defparts[1]?' ' . $defparts[1]:'') . '": SQLITE syntax error', E_USER_WARNING);
                    return false;
                }
                if ($severpos = strpos($createtesttableSQL, ' ' . $defparts[1] . ' ')) {
                    $nextcommapos = strpos($createtesttableSQL, ',', $severpos);
                    if ($nextcommapos)
                        $createtesttableSQL = substr($createtesttableSQL, 0, $severpos) . substr($createtesttableSQL, $nextcommapos + 1);
                    else
                        $createtesttableSQL = substr($createtesttableSQL, 0, $severpos - (strpos($createtesttableSQL, ',')?0:1)) . ')';
                    unset($newcols[$defparts[1]]);
                    /* RUBEM */ $createtesttableSQL = str_replace(",)", ")", $createtesttableSQL);
                } else {
                    trigger_error('unknown column "' . $defparts[1] . '" in "' . $table . '"', E_USER_WARNING);
                    return false;
                }
                break;

            case 'rename'; // RUBEM
                if (sizeof($defparts) < 2) {
                    trigger_error('near "' . $defparts[0] . ($defparts[1]?' ' . $defparts[1]:'') . '": SQLITE syntax error', E_USER_WARNING);
                    return false;
                }
                $newname = $defparts[2];
                break;

            default:

                trigger_error('near "' . $prevword . '": SQLITE syntax error', E_USER_WARNING);
                return false;
        } // switch
        $prevword = $defparts[sizeof($defparts)-1];
    } // foreach
    // This block of code generates a test table simply to verify that the columns specifed are valid
    // in an sql statement. This ensures that no reserved words are used as columns, for example
    sqlite_query($g_current_db, $createtesttableSQL);
    $err = sqlite_last_error($g_current_db);
    if ($err) {
        trigger_error("Invalid SQLITE code block: " . sqlite_error_string($err) . "\n", E_USER_WARNING);
        return false;
    }
    $droptempsql = 'DROP TABLE ' . $tmpname;
    sqlite_query($g_current_db, $droptempsql);
    // End test block
    // Is it a Rename?
    if (strlen($newname) > 0) {
        // $table = preg_replace("/([a-z]_)[a-z_]*/i", "\\1" . $newname, $table);
        // what do want with the regex? the expression should be [a-z_]! hans
        // why not just
        $table = $newname;
    }
    $createnewtableSQL = 'CREATE ' . substr(trim(preg_replace("'" . $tmpname . "'", $table, $createtesttableSQL, 1)), 17);

    $newcolumns = '';
    $oldcolumns = '';
    reset($newcols);

    while (list($key, $val) = each($newcols)) {
        $newcolumns .= ($newcolumns?', ':'') . $val;
        $oldcolumns .= ($oldcolumns?', ':'') . $key;
    }
    $copytonewsql = 'INSERT INTO ' . $table . '(' . $newcolumns . ') SELECT ' . $oldcolumns . ' FROM ' . $tmpname;
    // ------------------------------------- Perform the actions
    sqlite_query($g_current_db, $createtemptableSQL); //create temp table
    sqlite_query($g_current_db, $copytotempsql); //copy to table
    sqlite_query($g_current_db, $dropoldsql); //drop old table

    sqlite_query($g_current_db, $createnewtableSQL); //recreate original table
    sqlite_query($g_current_db, $copytonewsql); //copy back to original table
    sqlite_query($g_current_db, $droptempsql); //drop temp table

    return true;
}
// ------------------------------------------------------------------ END OF FILE

?>
