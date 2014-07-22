<?php

define ("NAME", 'rubikin_db');
define ("USER", 'root');
define ("PASS", '');
define ("IMPORT_FILE_NAME", "products1000.csv");

const TB_PRODUCT = 'product';
const TB_CATEGORY = 'category';
const TB_OPTION = 'rubikin_db.option';
const TB_OPTION_VALUE = 'option_value';
const TB_PRODUCT_OPTION = 'product_option';
const TB_PRODUCT_CATEGORY = 'product_category';

$mysqli = new mysqli("localhost", USER, PASS, NAME);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$options = getopt("f::x::");

if (isset($options['x'])) {
    // reset table
    resetTables($mysqli);
    exit('Reset database');
}

$mysqli->close();

if (empty($options['f'])) {
    exit ("Missing filename");
}

if (!file_exists($file = __DIR__ . "\week2\day1\\" . $options['f'])) {
    exit (sprintf("File %s does not exist", $file));
}

// Set time-out
// set_time_limit(300);

$now = microtime(true);
$memory = memory_get_usage();

// include the file to run
require($file);

echo "loading $file" . "\n";
echo "Total time: " . (microtime(true) - $now) . "\n";
echo "Total memory: " . memory_get_usage() - $memory . "\n";



/**
 * The section below is dedicated for simple functions to re-use in next step of the project
 */
function resetTables(mysqli $mysqli)
{
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
    $mysqli->query("TRUNCATE TABLE " . TB_PRODUCT_CATEGORY);
    $mysqli->query("TRUNCATE TABLE " . TB_PRODUCT_OPTION);
    $mysqli->query("TRUNCATE TABLE " . TB_OPTION_VALUE);
    $mysqli->query("TRUNCATE TABLE " . TB_CATEGORY);
    $mysqli->query("TRUNCATE TABLE " . TB_OPTION);
    $mysqli->query("TRUNCATE TABLE " . TB_PRODUCT);
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
}
