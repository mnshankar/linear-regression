<?php
/**
 * Created by PhpStorm.
 * User: nshankar
 * Date: 2/24/2015
 * Time: 10:48 AM
 */

class RegressionTest extends PHPUnit_Framework_TestCase{

    public function testCSVImportWorksCorrectly()
    {
        $reg = new \mnshankar\LinearRegression\Regression();
        //dependent variable is vas1.. column number 1
        //independent columns are indep1, indep2, indep3 and indep4.. column numbers 9,10,11 and 12
        $reg->loadCSV('tests/testfile.csv',array(1), array(9,10,11,12));
        $this->assertEquals($reg->getX(),$this->getXForTesting());
        $this->assertEquals($reg->getY(),$this->getYForTesting());
    }
    public function testRegressionComputation()
    {
        $reg = new \mnshankar\LinearRegression\Regression();
        $reg->setX($this->getXForTesting());
        $reg->setY($this->getYForTesting());
        $reg->Compute();
        $this->assertEquals(0.3956,$reg->getRSQUARE(),null,.01);
        $this->assertEquals(1.800187032,$reg->getF(),null,.01);
        $this->assertEquals(331.75,$reg->getSSTO());
        $this->assertEquals(200.5,$reg->getSSE());
        $this->assertEquals(131.25,$reg->getSSR());
        $this->assertEquals(0.628990651, $reg->getMultipleR(),null,.01);
        $this->assertEquals(16,$reg->getObservations());

        $stdErrors = $reg->getStandardError();
        $pValues = $reg->getPValues();
        $tStat = $reg->getTStats();
        $coefficients = $reg->getCoefficients();

        //The values to test against is obtained from excel for this data.
        //refer to attached excel workbook Regression_Verification.xlsx
        $coefficientsToTest = array(10,-1,0.75,-2.5,5);
        $stdErrorsToTest = array(6.492346893,2.134670509,2.134670509,2.134670509,2.134670509);
        $tStatToTest = array(1.54027506,-0.468456371,0.351342278,-1.171140928,2.342281855);
        $pValuesToTest = array(0.151751456,0.648604269,0.731968834,0.26628656,0.039014953);

        $this->assertEquals($coefficientsToTest, $coefficients,null,.01);
        $this->assertEquals($stdErrorsToTest,$stdErrors,null,.01);
        $this->assertEquals($tStatToTest,$tStat,null,.01);
        $this->assertEquals($pValuesToTest,$pValues,null,.01);

    }
    private function getXForTesting()
    {
       return array(
            array(1,2,1,2,2),
            array(1,2,2,2,2),
            array(1,2,1,2,1),
            array(1,2,2,2,1),
            array(1,2,1,1,2),
            array(1,2,2,1,2),
            array(1,2,1,1,1),
            array(1,2,2,1,1),
            array(1,1,1,2,2),
            array(1,1,2,2,2),
            array(1,1,1,2,1),
            array(1,1,2,2,1),
            array(1,1,1,1,2),
            array(1,1,2,1,2),
            array(1,1,1,1,1),
            array(1,1,2,1,1),
        );
    }
    private function getYForTesting()
    {
        return array(
            array(12),
            array(12),
            array(13),
            array(7),
            array(21),
            array(22),
            array(9),
            array(7),
            array(9),
            array(16),
            array(11),
            array(17),
            array(16),
            array(19),
            array(13),
            array(10),
        );
    }
} 