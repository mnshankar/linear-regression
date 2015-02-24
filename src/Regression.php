<?php
namespace mnshankar\LinearRegression;
/**
 * Copyright (c)  2011 Shankar Manamalkav <nshankar@ufl.edu>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Class for computing multiple linear regression of the form
 * y=a+b1x1+b2x2+b3x3...
 *
 * @author shankar<nshankar@ufl.edu>
 *
 *
 */
class Regression
{

    private $SSEScalar; //sum of squares due to error
    private $SSRScalar; //sum of squares due to regression
    private $SSTOScalar; //Total sum of squares
    private $RSquare;         //R square
    private $F;               //F statistic
    private $coefficients;    //regression coefficients array
    private $stderrors;    //standard errror array
    private $tstats;     //t statistics array
    private $pvalues;     //p values array
    private $x = array();
    private $y = array();
    private $multipleR;
    private $observations;

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setX($x)
    {
        $this->x = $x;
    }

    public function setY($y)
    {
        $this->y = $y;
    }

    public function getSSE()
    {
        return $this->SSEScalar;
    }

    public function getSSR()
    {
        return $this->SSRScalar;
    }

    public function getSSTO()
    {
        return $this->SSTOScalar;
    }

    public function getRSQUARE()
    {
        return $this->RSquare;
    }

    public function getMultipleR()
    {
        return $this->multipleR;
    }

    public function getObservations()
    {
        return $this->observations;
    }

    public function getF()
    {
        return $this->F;
    }

    public function getCoefficients()
    {
        return $this->coefficients;
    }

    public function getStandardError()
    {
        return $this->stderrors;
    }

    public function getTStats()
    {
        return $this->tstats;
    }

    public function getPValues()
    {
        return $this->pvalues;
    }

    /**
     * @example $reg->loadCSV('abc.csv',array(0), array(1,2,3));
     * @param string $file
     * @param array $dependentVariableColumn
     * @param array $independentVariableColumns
     * @param bool $hasHeader
     * @internal param array $xcolnumbers
     * @internal param type $ycolnumber
     */
    public function loadCSV($file, array $dependentVariableColumn,
                            array $independentVariableColumns, $hasHeader = true)
    {
        $xarray = array();
        $yarray = array();
        $rawData = array();
        $handle = fopen($file, "r");

        //if first row has headers.. skip the first row
        if ($hasHeader) {
            fgetcsv($handle);
        }
        //get the remaining data into an array
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rawData[] = $data;
        }
        $sampleSize = count($rawData);  //total number of rows

        $rowCounter = 0;
        while ($rowCounter < $sampleSize) {
            $xarray[] = $this->GetArray($rawData, $independentVariableColumns, $rowCounter, true);
            $yarray[] = $this->GetArray($rawData, $dependentVariableColumn, $rowCounter);   //y always has 1 col!
            $rowCounter++;
        }
        $this->x = $xarray;
        $this->y = $yarray;
    }

    private function GetArray($rawData, $colsToExtract, $row, $includeIntercept = false)
    {
        $returnArray = array();
        if ($includeIntercept) {
            //prepend an all 1's column for the intercept. Only for X value
            $returnArray[] = 1;
        }
        foreach ($colsToExtract as $key => $val) {
            $returnArray[] = $rawData[$row][$val];
        }
        return $returnArray;
    }

    public function Compute()
    {
        if ((count($this->x) == 0) || (count($this->y) == 0)) {
            throw new \Exception ('Please supply valid X and Y arrays');
        }
        $this->observations = count($this->x);

        $mx = new Matrix($this->x);
        $my = new Matrix($this->y);

        //coefficient(b) = (X'X)-1X'Y 
        $xTx = $mx->Transpose()->Multiply($mx)->Inverse();
        $xTy = $mx->Transpose()->Multiply($my);

        $coeff = $xTx->Multiply($xTy);

        $num_independent = $mx->NumColumns();   //note: intercept is included
        $sample_size = $mx->NumRows();
        $dfTotal = $sample_size - 1;
        $dfModel = $num_independent - 1;
        $dfResidual = $dfTotal - $dfModel;
        //create unit vector..
        for ($ctr = 0; $ctr < $sample_size; $ctr++)
            $u[] = array(1);

        $um = new Matrix($u);
        //SSR = b(t)X(t)Y - (Y(t)UU(T)Y)/n        
        //MSE = SSE/(df)
        $SSR = $coeff->Transpose()->Multiply($mx->Transpose())->Multiply($my)
            ->Subtract(
                ($my->Transpose()
                    ->Multiply($um)
                    ->Multiply($um->Transpose())
                    ->Multiply($my)
                    ->ScalarDivide($sample_size))
            );

        $SSE = $my->Transpose()->Multiply($my)->Subtract(
            $coeff->Transpose()
                ->Multiply($mx->Transpose())
                ->Multiply($my)
        );

        $SSTO = $SSR->Add($SSE);
        $this->SSEScalar = $SSE->GetElementAt(0, 0);
        $this->SSRScalar = $SSR->GetElementAt(0, 0);
        $this->SSTOScalar = $SSTO->GetElementAt(0, 0);

        $this->RSquare = $this->SSRScalar / $this->SSTOScalar;

        $this->multipleR = sqrt($this->getRSQUARE());

        $this->F = (($this->SSRScalar / ($dfModel)) / ($this->SSEScalar / ($dfResidual)));
        $MSE = $SSE->ScalarDivide($dfResidual);
        //this is a scalar.. get element
        $e = ($MSE->GetElementAt(0, 0));

        $stdErr = $xTx->ScalarMultiply($e);
        for ($i = 0; $i < $num_independent; $i++) {
            //get the diagonal elements
            $searray[] = array(sqrt($stdErr->GetElementAt($i, $i)));
            //compute the t-statistic
            $tstat[] = array($coeff->GetElementAt($i, 0) / $searray[$i][0]);
            //compute the student p-value from the t-stat
            $pvalue[] = array($this->Student_PValue($tstat[$i][0], $dfResidual));
        }
        //convert into 1-d vectors and store
        for ($ctr = 0; $ctr < $num_independent; $ctr++) {
            $this->coefficients[] = $coeff->GetElementAt($ctr, 0);
            $this->stderrors[] = $searray[$ctr][0];
            $this->tstats[] = $tstat[$ctr][0];
            $this->pvalues[] = $pvalue[$ctr][0];
        }
    }

    /**
     * @link http://home.ubalt.edu/ntsbarsh/Business-stat/otherapplets/pvalues.htm#rtdist
     * @param float $t_stat
     * @param float $deg_F
     * @return float
     */
    private function Student_PValue($t_stat, $deg_F)
    {
        $t_stat = abs($t_stat);
        $mw = $t_stat / sqrt($deg_F);
        $th = atan2($mw, 1);
        if ($deg_F == 1)
            return 1.0 - $th / (M_PI / 2.0);
        $sth = sin($th);
        $cth = cos($th);
        if ($deg_F % 2 == 1)
            return 1.0 - ($th + $sth * $cth * $this->statcom($cth * $cth, 2, $deg_F - 3, -1)) / (M_PI / 2.0);
        else
            return 1.0 - ($sth * $this->statcom($cth * $cth, 1, $deg_F - 3, -1));
    }

    /**
     * @link http://home.ubalt.edu/ntsbarsh/Business-stat/otherapplets/pvalues.htm#rtdist
     * @param float $q
     * @param float $i
     * @param float $j
     * @param float $b
     * @return float
     */
    private function statcom($q, $i, $j, $b)
    {
        $zz = 1;
        $z = $zz;
        $k = $i;
        while ($k <= $j) {
            $zz = $zz * $q * $k / ($k - $b);
            $z = $z + $zz;
            $k = $k + 2;
        }
        return $z;
    }

}

?>
