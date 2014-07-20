<?php
namespace www\week2\day1\dinhtuan\test;

use www\week2\day1\dinhtuan\ImportImprove;

// Init & Register Autoloader
require "\..\SplClassLoader.php";
$loader = new \SplClassLoader('www', 'D:\wamp');
$loader->register();

class ImportImproveTest extends \PHPUnit_Framework_TestCase
{
    private $instance;

    public function setUp()
    {
        $this->instance = new ImportImprove('testinginput.csv');
    }

    public function mappingCaseProvider()
    {
        return array(
                array('prod_id' , 9),
                array('prod_name' , 7),
                array('prod_slug' , 5),
                array('prod_short' ,4),
                array('prod_descr' , 12),
                array('prod_available' , 8),
                array('prod_create' , 0),
                array('prod_update' , 1),
                array('prod_delete' , 2),
                array('prod_method' , 6),
                array('category' , 3)
            );
    }

    /**
     * @dataProvider mappingCaseProvider
     */
    public function testSetMappingFunctionSetTheRightIndexToTheRightColumn($columnName, $expectedColumnIndex)
    {
        $mappingArray = array(
        'prod_id' => 9,
        'prod_name' => 7,
        'prod_slug' => 5,
        'prod_short' =>4,
        'prod_descr' => 12,
        'prod_available' => 8,
        'prod_create' => 0,
        'prod_update' => 1,
        'prod_delete' => 2,
        'prod_method' => 6,
        'category' => 3
        );
        
        $this->instance->setMapping($mappingArray);

        $setColIndex = \PHPUnit_Framework_Assert::getObjectAttribute($this->instance, 'colIndex');

        $this->assertEquals($setColIndex[$columnName], $expectedColumnIndex);
    }

    public function testGenerateCSVsFunctionGenerateTheRightCSVFileForProductTable()
    {
        $this->instance->generateCSVs();

        $this->assertFileExists('temp\product.csv');

        $this->assertStringEqualsFile('temp\product.csv',"'1','aothun','aodaman','aodemac','aothundemac','2014-07-21 12:00:00','2014-07-21 12:00:00','2014-07-21 12:00:00','2014-07-21 12:00:00','matched'\n");
    }

    public function testGenerateCSVsFunctionGenerateTheRightCSVFileForOptionTable()
    {
        $this->instance->generateCSVs();

        $this->assertFileExists('temp\option.csv');

        $this->assertStringEqualsFile('temp\option.csv',"'color'\n'size'\n");
    }

    public function testGenerateCSVsFunctionGenerateTheRightCSVFileForCategoryTable()
    {
        $this->instance->generateCSVs();

        $this->assertFileExists('temp\category.csv');

        $this->assertStringEqualsFile('temp\category.csv',"'trangphuc'\n");
    }

    public function testGenerateCSVsFunctionGenerateTheRightCSVFileForOptionValueTable()
    {
        $this->instance->generateCSVs();

        $this->assertFileExists('temp\optionvalue.csv');

        $this->assertStringEqualsFile('temp\optionvalue.csv',"'1','do'\n'1','trang'\n'1','xanh'\n'2','s'\n'2','m'\n'2','l'\n'2','xl'\n'2','xxl'\n");
    }

    public function testGenerateCSVsFunctionGenerateTheRightCSVFileForProductOptionTable()
    {
        $this->instance->generateCSVs();

        $this->assertFileExists('temp\productoption.csv');

        $this->assertStringEqualsFile('temp\productoption.csv',"'1','1'\n'1','2'\n'1','3'\n'1','4'\n'1','5'\n'1','6'\n'1','7'\n'1','8'\n");
    }

    public function testGenerateCSVsFunctionGenerateTheRightCSVFileForProductCategoryTable()
    {
        $this->instance->generateCSVs();

        $this->assertFileExists('temp\productcategory.csv');

        $this->assertStringEqualsFile('temp\productcategory.csv',"'1','1'\n");
    }

}