<?php
namespace www\week2\day1\dinhtuan;

class ImportImprove
{
    private $fileToImportName;
    private $nameOfProdFile = "product.csv";
    private $nameOfOptFile = "option.csv";
    private $nameOfCatFile = "category.csv";
    private $nameOfOptValFile = "optionvalue.csv";
    private $nameOfProdOptFile = "productoption.csv";
    private $nameOfProdCatFile = "productcategory.csv";
    private $pathOfTempRepo = 'temp\\';
    private $chunkSize = 0;

    private $stringOfProd = "";
    private $stringOfOpt = "";
    private $stringOfCat = "";
    private $stringOfOptVal = "";
    private $stringOfProdOpt = "";
    private $stringOfProdCat = "";

    const TB_PRODUCT = 'product';
    const TB_CATEGORY = 'category';
    const TB_OPTION = 'rubikin_db.option';
    const TB_OPTION_VALUE = 'option_value';
    const TB_PRODUCT_OPTION = 'product_option';
    const TB_PRODUCT_CATEGORY = 'product_category';
    const MAXCHUNKSIZE = 5000; 

    // Array for mapping
    private $colIndex = array(
        'prod_id' => 0,
        'prod_name' => 1,
        'prod_slug' => 2,
        'prod_short' =>3,
        'prod_descr' => 4,
        'prod_available' => 5,
        'prod_create' => 6,
        'prod_update' => 7,
        'prod_delete' => 8,
        'prod_method' => 9,
        'category' => 12
    );

    /**
     * get the fully and mysqli-operatable path string
     * @param string $string name of the .csv file that has just been generated.
     * @return string the fully and mysqli-operatable path string
     */
    private function getPath($string)
    {
        $concate = $this->pathOfTempRepo . $string;
        $result = str_replace("\\", "\\\\", $concate);
        return $result;
    }

    /**
     * write all the strings(e.g $stringOfProd, $stringOfOpt, ect )
     * into the actual .csv files(e.g product.csv, option.csv, ect )
     * @param handlers all the handlers for all the .csv files
     * @return void
     */
    private function writethemall($srcProd, $srcOptVal, $srcCat, $srcProdOpt, $srcProdCat)
    {
        fwrite($srcProd, $this->stringOfProd);
        fwrite($srcCat, $this->stringOfCat);
        fwrite($srcOptVal, $this->stringOfOptVal);
        fwrite($srcProdOpt, $this->stringOfProdOpt);
        fwrite($srcProdCat, $this->stringOfProdCat);

        $this->stringOfProd = "";
        $this->stringOfOptVal = "";
        $this->stringOfCat= "";
        $this->stringOfProdOpt = "";
        $this->stringOfProdCat = "";
    }

    /**
     * constructor: set the absolute path for the input file and the 
     * temp folder that contains the to-be-generated-*.csv files, and
     * also make the folder if it does not exist
     * @param string $name name of the file to import
     * @return void
     */
    public function __construct($name)
    {
        $this->fileToImportName = __DIR__ . "\\" . $name;
        $this->pathOfTempRepo = __DIR__ . "\\" . $this->pathOfTempRepo;
        if (!is_dir($this->pathOfTempRepo)) {
            mkdir($this->pathOfTempRepo);
        }
    }

    /**
     * Array use for mapping setter
     * @param array $array the array to map
     * @return void
     */
    public function setMapping($array)
    {
        $this->colIndex = $array;
    }

    /**
     * analyse the fields, get all the options then put them all in a option.csv file, 
     * and then import into the database later.
     * @param string $firstline the first line of the input .csv file, usually contains fields name.
     * @param handler $outHandler passing by reference, the output handler to write into the option.csv file. 
     * @return array that contains all the options and their respective column indexes.
     */
    private function optionProcessing($firstline, &$outHandler)
    {
        $fields = str_getcsv($firstline);

        $listOptionIndex = array();
        $listOptionName = array();

        // Get all the OPTION
        $numfields = count($fields);
        for ($i = 0; $i < $numfields; $i++ ) {
            if ("option_" == substr($fields[$i], 0, 7)) {
                $optName = substr($fields[$i], 7);
                $listOptionName []= $optName;
                $listOptionIndex []= $i;
            }
        }

        // Insert all the Options
        foreach ($listOptionName as $name) {
            fwrite($outHandler, "'" . $name . "'\n");
        }

        return $listOptionIndex;
    }

    /**
     * analyse the string, split it by CSV format, reformat the date/time type,
     * then put all the fields needed for a Product record in a string.
     * That string will be output into a product.csv file, and then import into the database later.
     * @param array $data data of a row from the input .csv file
     * @param string $outString passing by reference, the output string for the Product table.
     * @return void
     */
    private function productProcessing($data, &$outString)
    {
        
        // Format date/time data
        !$data[$this->colIndex['prod_available']] or $data[$this->colIndex['prod_available']] = date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_available']]));
        !$data[$this->colIndex['prod_create']] or $data[$this->colIndex['prod_create']] = date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_create']]));
        !$data[$this->colIndex['prod_update']] or $data[$this->colIndex['prod_update']] = date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_update']]));
        !$data[$this->colIndex['prod_delete']] or $data[$this->colIndex['prod_delete']] = date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_delete']]));

        // Insert into the  PRODUCT table
        $lineOfProduct = "'" . $data[$this->colIndex['prod_id']] . "','"
                             . $data[$this->colIndex['prod_name']] . "','"
                             . $data[$this->colIndex['prod_slug']] . "','"
                             . $data[$this->colIndex['prod_short']] . "','"
                             . $data[$this->colIndex['prod_descr']] . "','"
                             . $data[$this->colIndex['prod_available']] . "','"
                             . $data[$this->colIndex['prod_create']] . "','"
                             . $data[$this->colIndex['prod_update']] . "','"
                             . $data[$this->colIndex['prod_delete']] . "','"
                             . $data[$this->colIndex['prod_method']] . "'\n";

        $outString .= $lineOfProduct;
    }

    /**
     * analyse the string, split it by CSV format, proceed to check, set, get,
     * whatever is appropriate. Then out put to strings to later be imported into 
     * Option_Value table and Product_Option table.
     * @param array $data data of a row from the input .csv file
     * @param array $listOfOptionColIndex contains all the options and their respective column index
     * @param array and int $listOptionValue and $indexOfOptionValue passing by reference, use for checking unique and getting ID
     * @param string $outOptValString passing by reference, the output string for Option_Value table.
     * @param string $outProdOptString passing by reference, the output string for Product_Option table. 
     * @return void
     */
    private function optValAndProdOptProcessing($data, $listOfOptionColIndex, &$listOptionValue, &$indexOfOptionValue, &$outOptValString, &$outProdOptString)
    {
        $numOpt = count($listOfOptionColIndex);
        for ($i = 0; $i < $numOpt; $i++) {

            $optionColIndex = $listOfOptionColIndex[$i];

            if ("" != $data[$optionColIndex]) {

                $optionId = $i + 1;
                $optionValues = explode(";", $data[$optionColIndex]);

                foreach ($optionValues as $value) {
                    
                    $optionValueId = "";

                    if (!isset($listOptionValue[$optionId])) {
                        $listOptionValue[$optionId] = array();
                        $listOptionValue[$optionId][$value] = $indexOfOptionValue;
                        $optionValueId = $indexOfOptionValue;
                        $indexOfOptionValue++;

                        //Insert the option value
                        $outOptValString .= "'" . $optionId . "','" . $value . "'\n";

                    } elseif (!isset($listOptionValue[$optionId][$value])) {
                        $listOptionValue[$optionId][$value] = $indexOfOptionValue;
                        $optionValueId = $indexOfOptionValue;
                        $indexOfOptionValue++;

                        //Insert the option value
                        $outOptValString .= "'" . $optionId . "','" . $value . "'\n";

                    } else {
                        $optionValueId = $listOptionValue[$optionId][$value];
                    }

                    // Insert into the Product Option table
                    $outProdOptString .= "'" . $data[$this->colIndex['prod_id']] . "','" . $optionValueId . "'\n";
                }
            }
        }
    }

    /**
     * analyse .... to the Category and Product_Category table
     * @param array $data data of a row from the input .csv file
     * @param array and int $listCategoryName and $indexOfCategory passing by reference, use for checking unique and getting ID 
     * @param string $outCatString passing by reference, output string for Category table.
     * @param string $outProdCatString passing by reference, output string for Product_Category table
     * @return void
     */
    private function catAndProdCatProcessing($data, &$listCategoryName, &$indexOfCategory, &$outCatString, &$outProdCatString)
    {
        if ("" != $data[$this->colIndex['category']]) {

            $categoryName = explode(";", $data[$this->colIndex['category']]);

            foreach ($categoryName as $name) {
                
                $idOfCategory = "";

                if (!isset($listCategoryName[$name])) {
                    $listCategoryName[$name]= $indexOfCategory;
                    $idOfCategory = $indexOfCategory;
                    $indexOfCategory++;

                    // Insert into the CATEGORY table
                    $outCatString .= "'" . $name . "'\n";
                } else {
                    $idOfCategory = $listCategoryName[$name];
                }

                // Insert into the PRODUCT_CATEGORY table
                $outProdCatString .= "'" . $data[$this->colIndex['prod_id']] . "','" . $idOfCategory . "'\n";
            }
        }
    }

    /**
     * Generate CSV files for each tables for high-speed import later
     * @return void
     */
    public function generateCSVs()
    {
        $in = fopen($this->fileToImportName, 'r') or die("Cannot open file " . $this->fileToImportName);
        $outProduct = fopen($this->getPath($this->nameOfProdFile), 'w') or die("Cannot open file " . $this->nameOfProdFile);
        $outOption = fopen($this->getPath($this->nameOfOptFile), 'w') or die("Cannot open file " . $this->nameOfOptFile);
        $outCategory = fopen($this->getPath($this->nameOfCatFile), 'w') or die("Cannot open file " . $this->nameOfCatFile);
        $outOptVal = fopen($this->getPath($this->nameOfOptValFile), 'w') or die("Cannot open file " . $this->nameOfOptValFile);
        $outProdOpt = fopen($this->getPath($this->nameOfProdOptFile), 'w') or die("Cannot open file " . $this->nameOfProdOptFile);
        $outProdCat = fopen($this->getPath($this->nameOfProdCatFile), 'w') or die("Cannot open file " . $this->nameOfProdCatFile);

        $firstline = fgets($in);
        $listOfOptionColIndex = $this->optionProcessing($firstline, $outOption);

        // Arrays, variables use for checking unique, getting IDs and stuffs
        $listOptionValue = array();
        $indexOfOptionValue = 1;
        $listCategoryName = array();
        $indexOfCategory = 1;

        while (!feof($in)) {
            $row = fgets($in);
            $this->chunkSize++;
            if ("" != $row) {
                $data = str_getcsv($row);

                $this->productProcessing($data, $this->stringOfProd);           

                $this->optValAndProdOptProcessing($data, $listOfOptionColIndex, $listOptionValue, $indexOfOptionValue, $this->stringOfOptVal, $this->stringOfProdOpt);

                $this->catAndProdCatProcessing($data, $listCategoryName, $indexOfCategory, $this->stringOfCat, $this->stringOfProdCat);
            }

            if ($this->chunkSize = self::MAXCHUNKSIZE) {
                $this->writethemall($outProduct, $outOptVal, $outCategory, $outProdOpt, $outProdCat);
                $this->chunkSize = 0;
            }
        }

        if ($this->chunkSize > 0) {
                $this->writethemall($outProduct, $outOptVal, $outCategory, $outProdOpt, $outProdCat);
        }
    }

    /**
     * to not duplicate code
     * @return void
     */
    private function tinyImport($db, $string, $table, $fieldsToImport)
    {
        $sql = "LOAD DATA LOCAL INFILE '" . $string . "' IGNORE INTO TABLE " . $table .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n'" .
                $fieldsToImport;
        $db->query($sql) or die("Cannot import into table " . $table . ". Error: " . $db->error);
    }

    /**
     * to actually run query and import data into the database
     * @return void
     */
    public function import($db)
    {
        $fields = "(id,name,slug,short_description,description,available_on,created_at,updated_at,deleted_at,variant_selection_method)";
        $this->tinyImport($db, $this->getPath($this->nameOfProdFile), self::TB_PRODUCT, $fields);

        $fields = "(name)";
        $this->tinyImport($db, $this->getPath($this->nameOfOptFile), self::TB_OPTION, $fields);

        $fields = "(name)";
        $this->tinyImport($db, $this->getPath($this->nameOfCatFile), self::TB_CATEGORY, $fields);

        $fields = "(option_id,value)";
        $this->tinyImport($db, $this->getPath($this->nameOfOptValFile), self::TB_OPTION_VALUE, $fields);

        $fields = "(product_id,option_value_id)";
        $this->tinyImport($db, $this->getPath($this->nameOfProdOptFile), self::TB_PRODUCT_OPTION, $fields);

        $fields = "(product_id,category_id)";
        $this->tinyImport($db, $this->getPath($this->nameOfProdCatFile), self::TB_PRODUCT_CATEGORY, $fields);
    }
    
}