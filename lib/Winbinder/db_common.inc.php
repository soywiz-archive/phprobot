<?php

/**
 * WINBINDER - A native Windows binding for PHP
 *
 * Copyright © 2004-2005 Hypervisual - see LICENSE.TXT for details
 * Author: Rubem Pechansky (pechansky@hypervisual.com)
 *
 * Database wrapper functions for WinBinder
 */

/* TODO:
- Many optimizations
- Treat XML files as databases
- Treat INI files as databases: Each file a database, each section a table, each entry a row (CSV)
- Support other databases
*/
// ------------------------------------------------------------ DATABASE-SPECIFIC
// You may define APPPREFIX and WB_DATABASE in the application
if (!defined("APPPREFIX")) define("APPPREFIX", "");
if (!defined("WB_DATABASE")) define("WB_DATABASE", "SQLITE");

$_mainpath = pathinfo(__FILE__);
$_mainpath = $_mainpath["dirname"] . "/";

include $_mainpath . "db_" . strtolower(WB_DATABASE) . ".inc.php";
// -------------------------------------------------------------- TABLE FUNCTIONS
/**
 * db_table_exists()
 *
 * @param  $tablename of an opened database
 * @return "true" if table $tablename exists in the current database
 */
function db_table_exists($tablename)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    $result = db_query("SELECT * from " . APPPREFIX . $tablename);
    return is_resource($result);
}

/**
 * db_create_table()
 *
 * @param  $tablename
 * @param  $fieldnames ( beside "id" )
 * @param  $fieldattrib
 * @param string $idfield ( set to "id" )
 * @param array $valarray ( $valarray[0] = 1.record, $valarray[1] = 2.record, ... )
 * @return "TRUE" or "FALSE" if Table already exists, could not create Table, could not create Records
 */
function db_create_table($tablename, $fieldnames, $fieldattrib, $idfield = "id", $valarray = null)
{
    global $g_lasttable;

    if ($tablename == null || $tablename == "")
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    if (db_table_exists($tablename))
        return false;

    if (is_string($fieldnames))
        $fieldnames = preg_split("/[\r\n,]/", $fieldnames);
    if (is_string($fieldattrib))
        $fieldattrib = preg_split("/[\r\n,]/", $fieldattrib);
    $attribs = count($fieldattrib);
    if (count($fieldnames) != $attribs)
        trigger_error(__FUNCTION__ . ": both arrays must be same length.\n");

    $sql = "CREATE TABLE " . APPPREFIX . "$tablename (";
    $sql .= "$idfield int(11) NOT NULL PRIMARY KEY, ";
    for($i = 0; $i < $attribs; $i++)
    $sql .= $fieldnames[$i] . " " . $fieldattrib[$i] .
    ($i < $attribs - 1 ? ", " : "");
    $sql .= ")";
    // Send the sql command
    $result = db_query($sql);
    if (!$result)
        trigger_error(__FUNCTION__ . ": could not create table $tablename.");

    if ($valarray)
        foreach($valarray as $values)
        db_create_record($tablename, $fieldnames, $values, $idfield);

    return $result;
}

/**
 * db_delete_table()
 *
 * @param  $tablename
 * @return ( nothing )
 */
function db_delete_table($tablename)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    if ($tablename == null || $tablename == "")
        return;
    if (db_table_exists(APPPREFIX . $tablename))
        db_query("DROP table " . APPPREFIX . $tablename);
}

/**
 * db_list_table_fields()
 *
 * @param  $tablename
 * @return array with the names of the fields of table $tablename
 */
function db_list_table_fields($tablename)
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    $result = db_query("SELECT * FROM " . APPPREFIX . $tablename);
    if ($result) {
        $array = array_keys(db_fetch_array($result, FETCH_ASSOC));
        db_free_result($result);
        return $array;
    } else
        return null;
}
// ------------------------------------------------------------- RECORD FUNCTIONS
/* Insert a new record in table $tablename.

  $tablename			Table name. If NULL uses the table used in last function call.
  $fieldnames			Array or CSV string with field names, one per line. If NULL, affects all fields.
  $fieldvalues			Array or CSV string with field values, one per line.

  Returns:				id of the affected record.
*/

/**
 * db_create_record()
 *
 * Insert a new record in table $tablename.
 *
 * @param  $tablename Table name. If NULL uses the table used in last function call.
 * @param unknown $fieldnames Array or CSV string with field names, one per line.
 * @param unknown $fieldvalues Array or CSV string with field values, one per line.
 * @param string $idfield
 * @return id number of inserted Record, Null if not succeded
 */
function db_create_record($tablename, $fieldnames = null, $fieldvalues = null, $idfield = "id")
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    if (!$fieldnames && !$fieldvalues) {
        $fieldnames = db_list_table_fields($tablename);
        $fieldvalues = array_fill(0, count($fieldnames), 0);
    }

    if (!$fieldnames) {
        $fieldnames = db_list_table_fields($tablename);
        array_shift($fieldnames);
    }
    // Get next available index
    $sql = "SELECT max($idfield) FROM " . APPPREFIX . $tablename;
    $result = db_query($sql);
    $newid = (db_fetch_array($result, FETCH_NUM)) ;
    $newid = $newid[0] + 1;
    // Build the two arrays
    $names = is_string($fieldnames) ? preg_split("/[\r\n]/", $fieldnames) : $fieldnames;
    $values = is_string($fieldvalues) ? preg_split("/[\r\n]/", $fieldvalues) : $fieldvalues;
    if (count($names) != count($values)) {
        trigger_error(__FUNCTION__ . ": both arrays must be same length.\n");
        return null;
    }
    // Build the SQL query
    $nfields = count($names);
    $fieldnames = $names;
    $fieldvalues = $values;
    for($i = 0, $names = ""; $i < $nfields; $i++)
    $names .= $fieldnames[$i] . ($i < $nfields - 1 ? ", " : "");
    for($i = 0, $values = ""; $i < $nfields; $i++)
    $values .= "'" . db_escape_string($fieldvalues[$i]) . "'" . ($i < $nfields - 1 ? ", " : "");

    $sql = "INSERT INTO " . APPPREFIX . $tablename . " ($idfield, $names) VALUES ($newid, $values)";

    $result = db_query($sql);
    if (!$result)
        trigger_error(__FUNCTION__ . ": could not create new record in table $tablename.");
    return $newid;
}

/**
 * db_edit_record()
 *
 * Edits a record from table $tablename. If $id is null, zero or < 0, inserts a new record.
 *
 * @param  $tablename If NULL uses the table used in last function call.
 * @param integer $id
 * @param unknown $fieldnames Array or CSV string with field names, one per line. If NULL, affects all fields.
 * @param unknown $fieldvalues Array or CSV string with field values, one per line.
 * @param string $idfield
 * @return id of the affected record
 */
function db_edit_record($tablename, $id = 0, $fieldnames = null, $fieldvalues = null, $idfield = "id")
{
    global $g_lasttable;

    if ($id == null || $id <= 0) { // Create a new record
        return db_create_record($tablename, $fieldnames, $fieldvalues, $idfield);
    } else { // Edit existing record
        if (!$tablename)
            $tablename = $g_lasttable;
        $g_lasttable = $tablename;
        // Build the two arrays
        if (!$fieldnames && !$fieldvalues) {
            $fieldnames = db_list_table_fields($tablename);
            $fieldvalues = array_fill(0, count($fieldnames), 0);
        } else {
            $names = is_string($fieldnames) ? preg_split("/[\r\n]/", $fieldnames) : $fieldnames;
            $values = is_string($fieldvalues) ? preg_split("/[\r\n]/", $fieldvalues) : $fieldvalues;
        }
        if (count($names) != count($values)) {
            trigger_error(__FUNCTION__ . ": both arrays must be same length.\n");
            return null;
        }
        // Build the SQL query
        $nfields = count($names);
        for($i = 0, $str = ""; $i < $nfields; $i++) {
            $str .= $names[$i] . "='" . db_escape_string($values[$i]) . "'" .
            ($i < $nfields - 1 ? ", " : "");
        }

        $sql = "UPDATE " . APPPREFIX . "$tablename SET $str WHERE $idfield=$id";
        // Send the SQL command
        $result = db_query($sql);
        if (!$result) {
            trigger_error(__FUNCTION__ . ": could not edit record $id in table $tablename.");
            return null;
        }
        return $id;
    }
}

/**
 * db_delete_records()
 *
 * Delete record from table $tablename.
 *
 * @param  $tablename
 * @param  $idarray the id or id array
 * @return ( nothing )
 */
function db_delete_records($tablename, $idarray, $idfield = "id")
{
    global $g_lasttable;

    if ($idarray == null || $idarray <= 0)
        return;
    if (!is_array($idarray))
        $idarray = array($idarray);

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    foreach($idarray as $item) {
        // Send the SQL command
        $sql = "DELETE FROM " . APPPREFIX . $tablename . " WHERE $idfield = " . $item;
        $result = db_query($sql);
        if (!$result) {
            trigger_error(__FUNCTION__ . ": could not delete record $id in table $tablename.");
            return false;
        }
    }
    return true;
}

/**
 * db_swap_records()
 *
 * Swaps values from two records, including the id field or not according to $xchangeid.
 *
 * @param  $tablename
 * @param  $id1
 * @param  $id2
 * @param string $idfield
 * @param boolean $xchangeid
 * @return ( nothing )
 */
function db_swap_records($tablename, $id1, $id2, $idfield = "id", $xchangeid = true)
{
    global $g_lasttable;
    // Table name
    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;
    $table = APPPREFIX . "$tablename";
    // Build SQL strings
    $result = db_query("SELECT * FROM $table WHERE $idfield = $id1");
    $a = db_fetch_array($result, FETCH_ASSOC);
    $fieldvalues1 = array_values($a);
    $fieldnames1 = array_keys($a);
    array_shift($fieldvalues1);
    array_shift($fieldnames1);

    $result = db_query("SELECT * FROM $table WHERE $idfield = $id2");
    $a = db_fetch_array($result, FETCH_ASSOC);
    $fieldvalues2 = array_values($a);
    $fieldnames2 = array_keys($a);
    array_shift($fieldvalues2);
    array_shift($fieldnames2);
    // Exchange values
    db_edit_record($tablename, $id1, $fieldnames2, $fieldvalues2, $idfield);
    db_edit_record($tablename, $id2, $fieldnames1, $fieldvalues1, $idfield);
    // Exchange id's
    if ($xchangeid) {
        $unique = db_get_next_free_id($table);
        db_edit_record($tablename, $id1, array($idfield), array($unique), $idfield);
        db_edit_record($tablename, $id2, array($idfield), array($id1), $idfield);
        db_edit_record($tablename, $unique, array($idfield), array($id2), $idfield);
    }
}

/**
 * db_get_data()
 *
 * Reads data from table $tablename.
 *
 * $tablename		Table name. If NULL uses the table used in last function call.
 * $id				Identifier(s). May be an array or a CSV string
 * $col			Column(s) or field(s). May be an array or a CSV string
 * $where			Additional WHERE clause
 * $result_type	May be FETCH_ASSOC, FETCH_BOTH or FETCH_NUM
 * $idfield		Name of id field
 * $orderby		Additional ORDER BY clause
 *
 * $id		$col		returns
 * --------------------------------------------------------------------
 *
 * int		null		array with the whole record $id
 * int		str			the value of column $col from record $id
 * int		str[]		array with column values in array $col of record $id
 * int[]	null		array of arrays with values from all columns of the $id registers
 * int[]	str			array with the values of column $col from the $id registers
 * int[]	str[]		2-D array with the values of columns $col from the $id registers
 * null	null		array of arrays with the whole table
 * null	str			array with values of the $col column from the whole table
 * null	str[]		array of arrays with the values of the columns $col from all table
 *
 * @param  $tablename
 * @param unknown $id
 * @param unknown $col
 * @param string $where
 * @param unknown $result_type
 * @param string $idfield
 * @param string $orderby
 * @return
 */
function db_get_data($tablename, $id = null, $col = null, $where = "", $result_type = FETCH_NUM, $idfield = "id", $orderby = "")
{
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    if (is_array($col))
        $col = implode(",", $col);
    if ($col === null || $col === "")
        $col = "*";
    // Build the WHERE clause
    if ($id !== null) {
        if (is_string($id) && strstr($id, ",")) {
            $id = explode(",", $id);
        }
        if (is_array($id)) {
            $idcond = "";
            for($i = 0; $i < count($id); $i++)
            $idcond .= "$idfield = '{$id[$i]}'" . ($i < count($id) - 1 ? " OR " : "");
        } else
            $idcond = "$idfield = '$id'";

        $condition = $where ? " WHERE ($where) AND ($idcond)" : " WHERE ($idcond)";
    } else
        $condition = $where ? " WHERE ($where)" : "";

    $orderby = $orderby ? " ORDER BY $orderby" : "";
    // Do the query
    $sql = "SELECT $col FROM " . APPPREFIX . $tablename . $condition . $orderby;

    $result = db_query($sql);
    if (!$result)
        return null;
    // Loop to build the return array
    $array = array();
    while ($row = db_fetch_array($result, $result_type)) {
        if (count($row) == 1)
            $row = array_shift($row);
        $array[] = $row;
    }
    db_free_result($result);
    // Return the result
    if (!is_array($array))
        return $array;

    switch (count($array)) {
        case 0:
            return null;

        case 1:

            $test = $array; // Copy array
            $elem = array_shift($test); // 1st element of array...
            if (is_null($elem)) // ...is it null?
                return null; // Yes: return null
            if (is_scalar($elem)) // ...is it a scalar?
                return $elem; // Yes: return the element alone
            else
                return $array; // No: return the whole array
        default:
            return $array;
    }
}

/**
 * db_get_id()
 *
 * Returns the id of the record indexed by $index
 *
 * @param  $tablename
 * @param  $index
 * @param string $idfield
 * @return
 */
function db_get_id($tablename, $index, $idfield = "id")
{
    global $g_lasttable;

    if (!is_scalar($index)) {
        trigger_error(__FUNCTION__ . ": index must be an integer");
        return null;
    } else
        $index = (int)$index;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;
    // Do the query
    $sql = "SELECT $idfield FROM " . APPPREFIX . $tablename . " LIMIT 1 OFFSET $index";

    $result = db_query($sql);
    if (!$result)
        return null;

    $ret = db_fetch_array($result, FETCH_NUM);

    db_free_result($result);
    return $ret[0];
}

/**
 * db_get_next_free_id()
 *
 * Returns the next available id in table $tablename.
 *
 * @param  $tablename
 * @param string $idfield
 * @return
 */
function db_get_next_free_id($tablename, $idfield = "id")
{
    global $g_current_db;
    global $g_lasttable;

    if (!$tablename)
        $tablename = $g_lasttable;
    $g_lasttable = $tablename;

    $sql = "SELECT max($idfield) FROM " . APPPREFIX . $tablename;
    $result = db_query($sql);
    $maxid = (db_fetch_array($result, FETCH_NUM)) ;

    return $maxid[0] + 1;
}
// ------------------------------------------------------------------ END OF FILE

?>
