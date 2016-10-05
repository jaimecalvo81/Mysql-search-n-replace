<html>
<head>
<script type="text/javascript">

    function do_this(){

        var checkboxes = document.getElementsByName('selected[]');
        var button = document.getElementById('toggle');

        if (button.value == 'select all') {
            for (var i in checkboxes) {
                checkboxes[i].checked = 'FALSE';
            }
            button.value = 'deselect all'
        } else {
            for (var i in checkboxes) {
                checkboxes[i].checked = '';
            }
            button.value = 'select all';
        }
    }
</script>
</head>
<body>
<?php
// Find and replace facility for complete MySQL database
//
// Written by Mark Jackson @ MJDIGITAL
// http://www.mjdigital.co.uk/blog
//
// Modified by Jaime Calvo

// Pick up the form data and assign it to variables

// SEARCH FOR
$search = $_POST['searchFor'];

// REPLACE WITH
$replace = $_POST['replaceWith'];

// DB Details
$hostname = $_POST['hostname'];
$database = $_POST['database'];
$username = $_POST['username'];
$password = $_POST['password'];

// Query Type: 'search' or 'replace'
$queryType = $_POST['queryType'];

// show errors  - true/false
$showErrors = true;

if($showErrors) {
    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors',1);
}

// Create connection to DB
$mysqli = new mysqli($hostname, $username, $password, $database, 3306);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

// Get list of tables
$table_sql = 'SHOW TABLES';
$table_q = mysqli_query($mysqli, $table_sql) or die("Cannot Query DB: ".mysql_error());
$tables_r = mysqli_fetch_assoc($table_q);
$tables = array();

// checkboxes for tables

if(!isset($_POST['selected'])){
?>
    <form method="post" action="mysql-replace.php" class="labelsRightAligned hintsTooltip">
        <input type="hidden" name="searchFor" value="<?php echo $_POST['searchFor']; ?>" />
        <input type="hidden" name="replaceWith" value="<?php echo $_POST['replaceWith']; ?>" />
        <input type="hidden" name="hostname" value="<?php echo $_POST['hostname']; ?>" />
        <input type="hidden" name="database" value="<?php echo $_POST['database']; ?>" />
        <input type="hidden" name="username" value="<?php echo $_POST['username']; ?>" />
        <input type="hidden" name="password" value="<?php echo $_POST['password']; ?>" />
        <input type="hidden" name="queryType" value="<?php echo $_POST['queryType']; ?>" />
        <fieldset style="height: 300px;overflow:auto">
        <legend>Select tables</legend>
        <div id="queryType" class="oneField">
            <input type="button" id="toggle" value="select all" onClick="do_this()" />
            <?php
                do{
                    echo '<div style="clear:both">
                            <input name="selected[]" type="checkbox" value="'.$tables_r['Tables_in_'.strtolower($database)].'" />
                            <label class="radio" for="selected[]">'.$tables_r['Tables_in_'.strtolower($database)].'</label>
                            </div>';
                }while($tables_r = mysqli_fetch_assoc($table_q));
            ?>
       </div>
        </fieldset>
        <div class="actions">
            <input type="submit" class="primaryAction" id="submit" name="submitAction" value="start" />
            <input type="button" class="secondaryAction" onclick="history.go(-1)" value="cancel" />
        </div>
    </form>

<?php
die;
} 

do{
    if(in_array($tables_r['Tables_in_'.strtolower($database)], $_POST['selected'])) $tables[] = $tables_r['Tables_in_'.strtolower($database)];
}while($tables_r = mysqli_fetch_assoc($table_q));


// create array to hold required SQL
$use_sql = array();

$rowHeading = ($queryType=='replace') ? 
        'Replacing \''.$search.'\' with \''.$replace.'\' in \''.$database."'\n\nSTATUS    |    ROWS AFFECTED    |    TABLE/FIELD    (+ERROR)\n"
      : 'Searching for \''.$search.'\' in \''.$database."'\n\nSTATUS    |    ROWS CONTAINING    |    TABLE/FIELD    (+ERROR)\n";

$output = $rowHeading;

$summary = '';

// LOOP THROUGH EACH TABLE
foreach($tables as $table) {
    // GET A LIST OF FIELDS
    $field_sql = 'SHOW FIELDS FROM '.$table;
    $field_q = mysqli_query($mysqli,$field_sql);
    $field_r = mysqli_fetch_assoc($field_q);

    // compile + run SQL
    do {
        $field = $field_r['Field'];
        $type = $field_r['Type'];
        switch(true) {
            // set which column types can be replaced/searched
            case stristr(strtolower($type),'char'): $typeOK = true; break;
            case stristr(strtolower($type),'text'): $typeOK = true; break;
            case stristr(strtolower($type),'blob'): $typeOK = true; break;
            case stristr(strtolower($field_r['Key']),'pri'): $typeOK = false; break; // do not replace on primary keys
            default: $typeOK = false; break;
        }

        if($typeOK) { // Field type is OK ro replacement
            // create unique handle for update_sql array
            $handle = $table.'_'.$field;
            if($queryType=='replace') {
                $sql[$handle]['sql'] = 'UPDATE '.$table.' SET '.$field.' = REPLACE('.$field.',\''.$search.'\',\''.$replace.'\')';
            } else {
                $sql[$handle]['sql'] = 'SELECT * FROM '.$table.' WHERE '.$field.' LIKE "'.$search.'"';
            }

            // execute SQL
            $error = false;
            $query = mysqli_query($mysqli,$sql[$handle]['sql']) or $error = mysqli_error();

            // store the output (just in case)
            $sql[$handle]['result'] = $query;
            $sql[$handle]['error'] = $error;

            if (isset($sql[$handle]['result']->num_rows)){
                $row_count = $sql[$handle]['result']->num_rows;
            } else {
                $row_count = 0;
            }

            // Write out Results into $output
            $output .= ($query) ? 'OK        ' : '--        ';
            $output .= ($row_count>0) ? '<strong>'.$row_count.'</strong>            ' : '<span style="color:#CCC">'.$row_count.'</span>            ';
            $fieldName = '`'.$table.'`.`'.$field.'`';
            $output .= $fieldName;
            if(60-strlen($fieldName) < 0){
                $a = 0;
            } else {
                $a = 60-strlen($fieldName);
            }
            $erTab = str_repeat(' ', $a );
            $output .= ($error) ? $erTab.'(ERROR: '.$error.')' : '';

            $output .= "\n";
        }
    }while($field_r = mysqli_fetch_assoc($field_q));
}

// write the output out to the page
echo '<pre>';
echo $output."\n";
echo '<pre>';
?>
<a href="index.php">back</a>
</body>
</html>
