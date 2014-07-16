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
	private $pathOfTempRepo = '\temp\\';
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

    private function getPath($string)
    {
        $concate = $this->pathOfTempRepo . $string;
        $result = str_replace("\\", "\\\\", $concate);
        return $result;
    }

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
	 * constructor
	 * @param string $name name of the file to import
	 * @return void
	 */
	public function __construct($name)
	{
		$this->fileToImportName = __DIR__ . "\\" . $name;
        $this->pathOfTempRepo = __DIR__ . $this->pathOfTempRepo;
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
	 * Generate CSV files for each tables for high-speed import later
	 *@return void
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
		$fields = str_getcsv($firstline);

		// Arrays, variables use for getting IDs and stuffs
        $listOptionIndex = array();
        $listOptionName = array();
        $listOptionValue = array();
        $indexOfOptionValue = 1;
        $listCategoryName = array();
        $indexOfCategory = 1;

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
        	fwrite($outOption, "'" . $name . "'\n");
        }

        while (!feof($in)) {
        	$row = fgets($in);
            $this->chunkSize++;
            if ("" != $row) {

            	$data = str_getcsv($row);

                // THINGS RELATE TO PRODUCT TABLE

            	// Format date/time data
                $data[$this->colIndex['prod_available']] = ("" == $data[$this->colIndex['prod_available']]) ? $data[$this->colIndex['prod_available']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_available']]));
                $data[$this->colIndex['prod_create']] = ("" == $data[$this->colIndex['prod_create']]) ? $data[$this->colIndex['prod_create']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_create']]));
                $data[$this->colIndex['prod_update']] = ("" == $data[$this->colIndex['prod_update']]) ? $data[$this->colIndex['prod_update']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_update']]));
                $data[$this->colIndex['prod_delete']] = ("" == $data[$this->colIndex['prod_delete']]) ? $data[$this->colIndex['prod_delete']] : date('Y-m-d h:i:s', strtotime($data[$this->colIndex['prod_delete']]));

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

                $this->stringOfProd .= $lineOfProduct;

                // END OF THINGS RELATE TO PRODUCT TABLE

                // THINGS RELATE TO OPTION_VALUE TABLE AND PRODUCT_OPTION TABLE

                // Loop through the OPTION columns
                $numOpt = count($listOptionIndex);
                for ($i = 0; $i < $numOpt; $i++) {

                	$optionColIndex = $listOptionIndex[$i];

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
                                $this->stringOfOptVal .= "'" . $optionId . "','" . $value . "'\n";

                            } elseif (!isset($listOptionValue[$optionId][$value])) {
                                $listOptionValue[$optionId][$value] = $indexOfOptionValue;
                                $optionValueId = $indexOfOptionValue;
                                $indexOfOptionValue++;

                                //Insert the option value
                                $this->stringOfOptVal .= "'" . $optionId . "','" . $value . "'\n";

                            } else {
                                $optionValueId = $listOptionValue[$optionId][$value];
                            }

                            // Insert into the Product Option table
                            $this->stringOfProdOpt .= "'" . $data[$this->colIndex['prod_id']] . "','" . $optionValueId . "'\n";
                		}
                	}
                }

                // END OF THINGS RELATE TO OPTION_VALUE AND PRODUCT_OPTION TABLE

                // THINGS RELATE TO CATEGORY TABLE AND PRODUCT_CATEGORY TABLE

                if ("" != $data[$this->colIndex['category']]) {

                    $categoryName = explode(";", $data[$this->colIndex['category']]);

                    foreach ($categoryName as $name) {
                        
                        $idOfCategory = "";

                        if (!isset($listCategoryName[$name])) {
                            $listCategoryName[$name]= $indexOfCategory;
                            $idOfCategory = $indexOfCategory;
                            $indexOfCategory++;

                            // Insert into the CATEGORY table
                            $this->stringOfCat .= "'" . $name . "'\n";
                        } else {
                            $idOfCategory = $listCategoryName[$name];
                        }

                        // Insert into the PRODUCT_CATEGORY table
                        $this->stringOfProdCat .= "'" . $data[$this->colIndex['prod_id']] . "','" . $idOfCategory . "'\n";
                    }
                }

                // END OF THINGS RELATE TO CATEGORY TABLE AND PRODUCT_CATEGORY TABLE
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

    private function tinyImport($db, $string, $table, $fieldsToImport)
    {
        $sql = "LOAD DATA LOCAL INFILE '" . $string . "' IGNORE INTO TABLE " . $table .
                " FIELDS TERMINATED BY ',' ENCLOSED BY '\''
                LINES TERMINATED BY '\n'" .
                $fieldsToImport;
        $db->query($sql) or die("Cannot import into table " . $table . ". Error: " . $db->error);
    }

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