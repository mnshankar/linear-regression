<?php
/**
 * Created by PhpStorm.
 * User: nshankar
 * Date: 2/24/2015
 * Time: 12:34 PM
 */

namespace mnshankar\LinearRegression;


class MatrixTest extends \PHPUnit_Framework_TestCase
{

    public function testCanCreate2dMatrix()
    {
        $arr = array(
            array(1, 2),
            array(3, 4)
        );
        $m = new Matrix($arr);
        $this->assertInstanceOf('mnshankar\LinearRegression\Matrix', $m);
    }

    /**
     * test console display function to ensure 100% code coverage
     */
    public function testMatrixDisplay()
    {
        $arr = array(
            array(1, 2),
            array(3, 4)
        );
        $m = new Matrix($arr);
        $stringDisplay = $m->DisplayMatrix();
        $this->assertSame("Order of the matrix is (2 rows X 2 columns)\n12\n34\n", $stringDisplay);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidMatrixException()
    {
        $arr = array(
            array(2, 3),
            array(1)
        );
        $m = new Matrix($arr);
    }

    public function testNotSquare()
    {
        $arr = array(array(1, 2), array(3, 4), array(5, 6));
        $m = new Matrix($arr);
        $this->assertFalse($m->isSquareMatrix());
    }

    public function testAdd()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->Add($mat2);

        $testRet = array(array(2, 4, 6, 8), array(8, 10, 12, 14));
        $this->assertSame($ret->GetInnerArray(), $testRet);
    }

    /**
     * @expectedException Exception
     */
    public function testAddException()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3)
        );
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $mat1->Add($mat2);
    }

    public function testSubtract()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->Subtract($mat2);

        $testRet = array(array(0, 0, 0, 0), array(0, 0, 0, 0));
        $this->assertSame($ret->GetInnerArray(), $testRet);
    }

    /**
     * @expectedException Exception
     */
    public function testSubtractException()
    {
        $arr1 = array(
            array(1, 2, 3, 4),
            array(4, 5, 6, 7)
        );
        $arr2 = array(
            array(1, 2, 3)
        );
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $mat1->Subtract($mat2);
    }

    public function testMultiply()
    {
        $arr1 = array(
            array(2, 0, -1, 1),
            array(1, 2, 0, 1)
        );
        $arr2 = array(
            array(1, 5, -7),
            array(1, 1, 0),
            array(0, -1, 1),
            array(2, 0, 0),
        );
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $ret = $mat1->Multiply($mat2);

        $testRet = array(array(4, 11, -15), array(5, 7, -7));
        $this->assertSame($ret->GetInnerArray(), $testRet);
    }

    /**
     * @expectedException Exception
     */
    public function testMultiplyException()
    {
        $arr1 = array(
            array(2, 0, -1, 1),
            array(1, 2, 0, 1)
        );
        $arr2 = array(
            array(1, 5, -7),
            array(1, 1, 0),
            array(0, -1, 1)
        );
        $mat1 = new Matrix($arr1);
        $mat2 = new Matrix($arr2);
        $mat1->Multiply($mat2);
        //the above should throw an exception!
    }

    public function testDeterminant()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4)
        );
        $mat1 = new Matrix($arr1);
        $det = $mat1->Determinant();
        $this->assertSame(-2, $det);
    }

    /**
     * @expectedException Exception
     */
    public function testDeterminantException()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4),
            array(5, 6)
        );
        $mat1 = new Matrix($arr1);
        $mat1->Determinant();
    }

    public function testTranspose()
    {
        $arr1 = array(
            array(1, 2),
            array(3, 4)
        );
        $mat1 = new Matrix($arr1);
        $t = $mat1->Transpose();
        $testarr = array(array(1, 3), array(2, 4));
        $this->assertSame($t->GetInnerArray(), $testarr);
    }

    public function testInverse()
    {
        $arr1 = array(
            array(4, 3),
            array(3, 2)
        );
        $mat1 = new Matrix($arr1);
        $t = $mat1->Inverse()->GetInnerArray();
        $exp = array(
            array(-2, 3),
            array(3, -4)
        );
        $this->assertSame($exp, $t);
    }

    /**
     * @expectedException Exception
     */
    public function testInverseException()
    {
        $arr1 = array(
            array(4, 3),
            array(3, 2),
            array(4, 5)
        );
        $mat1 = new Matrix($arr1);
        $mat1->Inverse()->GetInnerArray();
    }
} 