<?php
ob_start();

$paths = parse_ini_file("defines.php");

date_default_timezone_set("Europe/Istanbul");
setlocale(LC_TIME,'turkish');

require_once($paths['path'].$paths['main']."/dbOP/DataScraping.class.php");
require_once($paths['path'].$paths['main']."/dbOP/Db.class.php");
require_once($paths['path'].$paths['main']."/dbOP/User.class.php");
require_once($paths['path'].$paths['main']."/dbOP/Clan.class.php");
require_once($paths['path'].$paths['main']."/dbOP/Progress.class.php");


function temizleGet($veri){
    return trim(strip_tags(htmlentities(str_replace("'", "\'", $veri))));
}

function temizle($veri){
    return trim(strip_tags($veri));
}

function exportDatabase($host,$user,$pass,$name,  $tables=false, $backup_name=false )
{
    $mysqli = new mysqli($host,$user,$pass,$name);
    $mysqli->select_db($name);
    $mysqli->query("SET NAMES 'utf8'");

    $queryTables    = $mysqli->query('SHOW TABLES');
    while($row = $queryTables->fetch_row())
    {
        $target_tables[] = $row[0];
    }
    if($tables !== false)
    {
        $target_tables = array_intersect( $target_tables, $tables);
    }

    $content = "";

    foreach($target_tables as $table)
    {
        $result         =   $mysqli->query('SELECT * FROM '.$table);
        $fields_amount  =   $result->field_count;
        $rows_num=$mysqli->affected_rows;

        $content        .= "\n\n".'DELETE FROM '.$table.";\n\n";


        for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0)
        {
            while($row = $result->fetch_row())
            { //when started (and every after 100 command cycle):
                if ($st_counter%100 == 0 || $st_counter == 0 )
                {
                    $content .= "\nINSERT INTO ".$table." VALUES";
                }
                $content .= "\n(";
                for($j=0; $j<$fields_amount; $j++)
                {
                    $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) );
                    if (isset($row[$j]))
                    {
                        $content .= '"'.$row[$j].'"' ;
                    }
                    else
                    {
                        $content .= '""';
                    }
                    if ($j<($fields_amount-1))
                    {
                        $content.= ',';
                    }
                }
                $content .=")";
                //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num)
                {
                    $content .= ";";
                }
                else
                {
                    $content .= ",";
                }
                $st_counter=$st_counter+1;
            }
        } $content .="\n\n\n";
    }
    //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
    $backup_name = $backup_name ? $backup_name : $name.".sql";

    file_put_contents('/var/www/app/sql/mir4_db.sql', $content);
}

function importDatabase($mysqlHost,$mysqlUser,$mysqlPassword,$mysqlDatabase)
{
    // Name of the data file
    $filename = '/var/www/app/sql/mir4_db.sql';


// Connect to MySQL server
    $link = mysqli_connect($mysqlHost, $mysqlUser, $mysqlPassword, $mysqlDatabase);
    $link->query("SET NAMES 'utf8'");



    $tempLine = '';
// Read in the full file
    $lines = file($filename);
// Loop through each line
    foreach ($lines as $line) {

        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;

        // Add this line to the current segment
        $tempLine .= $line;
        // If its semicolon at the end, so that is the end of one query
        if (substr(trim($line), -1, 1) == ';')  {
            // Perform the query
            mysqli_query($link, $tempLine);
            // Reset temp variable to empty
            $tempLine = '';
        }
    }
}
?>