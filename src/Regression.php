<?php
/**
 * Contains class Regression.
 *
 * Class for computing multiple linear regression of the form
 * y=a+b1x1+b2x2+b3x3...
 *
 * PHP version 5.4
 *
 * LICENSE:
 * Copyright (c) 2011 Shankar Manamalkav <nshankar@ufl.edu>
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
 * @author    shankar<nshankar@ufl.edu>
 * @author    Michael Cummings<mgcummings@yahoo.com>
 * @copyright 2011 Shankar Manamalkav
 */
namespace mnshankar\LinearRegression;

class Regression
{
    /**
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RangeException
     */
    public function compute()
    {
        if (0 === count($this->getX()) || 0 === count($this->getY())) {
            throw new \LogicException('Please supply valid X and Y arrays');
        }
        $this->observations = count($this->getX());
        $mx = new Matrix($this->getX());
        $my = new Matrix($this->getY());
        //coefficient(b) = (X'X)-1X'Y
        $xTx = $mx->transpose()
                  ->multiply($mx)
                  ->inverse();
        $xTy = $mx->transpose()
                  ->multiply($my);
        $coeff = $xTx->multiply($xTy);
        //note: intercept is included
        $num_independent = $mx->numColumns();
        $sample_size = $mx->numRows();
        $dfTotal = $sample_size - 1;
        $dfModel = $num_independent - 1;
        $dfResidual = $dfTotal - $dfModel;
        //create unit vector..
        $um = new Matrix(array_fill(0, $sample_size, [1]));
        //SSR = b(t)X(t)Y - (Y(t)UU(T)Y)/n
        //MSE = SSE/(df)
        $SSR = $coeff->transpose()
                     ->multiply($mx->transpose())
                     ->multiply($my)
                     ->subtract(
                         $my->transpose()
                            ->multiply($um)
                            ->multiply($um->transpose())
                            ->multiply($my)
                            ->scalarDivide($sample_size)
                     );
        $SSE = $my->transpose()
                  ->multiply($my)
                  ->subtract(
                      $coeff->transpose()
                            ->multiply($mx->transpose())
                            ->multiply($my)
                  );
        $SSTO = $SSR->add($SSE);
        $this->SSEScalar = $SSE->getElementAt(0, 0);
        $this->SSRScalar = $SSR->getElementAt(0, 0);
        $this->SSTOScalar = $SSTO->getElementAt(0, 0);
        $this->rSquare = $this->SSRScalar / $this->SSTOScalar;
        $this->multipleR = sqrt($this->getRSquare());
        $this->f = (($this->SSRScalar / $dfModel) / ($this->SSEScalar / $dfResidual));
        $MSE = $SSE->scalarDivide($dfResidual);
        //this is a scalar.. get element
        $e = $MSE->getElementAt(0, 0);
        $stdErr = $xTx->scalarMultiply($e);
        $seArray = [];
        $tStat = [];
        $pValue = [];
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $num_independent; $i++) {
            //get the diagonal elements
            $seArray[] = [sqrt($stdErr->getElementAt($i, $i))];
            //compute the t-statistic
            $tStat[] = [$coeff->getElementAt($i, 0) / $seArray[$i][0]];
            //compute the student p-value from the t-stat
            $pValue[] = [$this->student_PValue($tStat[$i][0], $dfResidual)];
        }
        //convert into 1-d vectors and store
        /** @noinspection ForeachInvariantsInspection */
        for ($ctr = 0; $ctr < $num_independent; $ctr++) {
            $this->coefficients[] = $coeff->getElementAt($ctr, 0);
            $this->stdErrors[] = $seArray[$ctr][0];
            $this->tStats[] = $tStat[$ctr][0];
            $this->pValues[] = $pValue[$ctr][0];
        }
    }
    /**
     * @return array
     */
    public function getCoefficients()
    {
        return $this->coefficients;
    }
    /**
     * @return int|float
     */
    public function getF()
    {
        return $this->f;
    }
    /**
     * @return float|int
     */
    public function getMultipleR()
    {
        return $this->multipleR;
    }
    /**
     * @return int
     */
    public function getObservations()
    {
        return $this->observations;
    }
    /**
     * @return array
     */
    public function getPValues()
    {
        return $this->pValues;
    }
    /**
     * @return float|int
     */
    public function getRSquare()
    {
        return $this->rSquare;
    }
    /**
     * @return float|int
     */
    public function getSSEScalar()
    {
        return $this->SSEScalar;
    }
    /**
     * @return float|int
     */
    public function getSSRScalar()
    {
        return $this->SSRScalar;
    }
    /**
     * @return float|int
     */
    public function getSSTOScalar()
    {
        return $this->SSTOScalar;
    }
    /**
     * @return array
     */
    public function getStdErrors()
    {
        return $this->stdErrors;
    }
    /**
     * @return array
     */
    public function getTStats()
    {
        return $this->tStats;
    }
    /**
     * @return array
     */
    public function getX()
    {
        return $this->x;
    }
    /**
     * @return array
     */
    public function getY()
    {
        return $this->y;
    }
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @example  $reg->loadCSV('abc.csv',array(0), array(1,2,3));
     *
     * @param string $file
     * @param array  $dependentVariableColumn
     * @param array  $independentVariableColumns
     * @param bool   $hasHeader
     *
     * @throws \InvalidArgumentException
     */
    public function loadCSV(
        $file,
        array $dependentVariableColumn,
        array $independentVariableColumns,
        $hasHeader = true
    ) {
        $xArray = [];
        $yArray = [];
        $rawData = [];
        $handle = fopen($file, 'rb');
        if (false === $handle) {
            throw new \InvalidArgumentException('Could not open CSV file ' . $file);
        }
        //if first row has headers.. skip the first row
        if ($hasHeader && !feof($handle)) {
            fgetcsv($handle);
        }
        //get the remaining data into an array
        while (false !== ($data = fgetcsv($handle))) {
            $rawData[] = $data;
        }
        fclose($handle);
        $sampleSize = count($rawData);  //total number of rows
        if (0 === $sampleSize) {
            throw new \InvalidArgumentException('Received empty CSV file ' . $file);
        }
        for ($i = 0; $i < $sampleSize; ++$i) {
            $xArray[] = $this->getXArray($rawData, $independentVariableColumns, $i);
            //y always has 1 col!
            $yArray[] = $this->getYArray($rawData, $dependentVariableColumn, $i);
        }
        $this->setX($xArray);
        $this->setY($yArray);
    }
    /**
     * @param array $x
     *
     * @throws \InvalidArgumentException
     */
    public function setX(array $x)
    {
        if (0 === count($x)) {
            throw new \InvalidArgumentException('X can not but empty');
        }
        $this->x = $x;
    }
    /**
     * @param array $y
     *
     * @throws \InvalidArgumentException
     */
    public function setY(array $y)
    {
        if (0 === count($y)) {
            throw new \InvalidArgumentException('Y can not but empty');
        }
        $this->y = $y;
    }
    /**
     * @param array $rawData
     * @param array $colsToExtract
     * @param int   $row
     *
     * @return array
     */
    private function getXArray(array $rawData, array $colsToExtract, $row)
    {
        $returnArray = [1];
        foreach ($colsToExtract as $key => $val) {
            $returnArray[] = $rawData[$row][$val];
        }
        return $returnArray;
    }
    /**
     * @param array $rawData
     * @param array $colsToExtract
     * @param int   $row
     *
     * @return array
     */
    private function getYArray(array $rawData, array $colsToExtract, $row)
    {
        $returnArray = [];
        foreach ($colsToExtract as $key => $val) {
            $returnArray[] = $rawData[$row][$val];
        }
        return $returnArray;
    }
    /**
     * @link http://home.ubalt.edu/ntsbarsh/Business-stat/otherapplets/pvalues.htm#rtdist
     *
     * @param float $t_stat
     * @param float $deg_F
     *
     * @return float
     */
    private function student_PValue($t_stat, $deg_F)
    {
        $t_stat = (float)abs($t_stat);
        $mw = $t_stat / sqrt($deg_F);
        $th = atan2($mw, 1);
        if ($deg_F === 1.0) {
            return 1.0 - $th / (M_PI / 2.0);
        }
        $sth = sin($th);
        $cth = cos($th);
        if ($deg_F % 2 === 1) {
            return 1.0 - ($th + $sth * $cth * $this->statCom($cth * $cth, 2, $deg_F - 3, -1)) / (M_PI / 2.0);
        } else {
            return 1.0 - ($sth * $this->statCom($cth * $cth, 1, $deg_F - 3, -1));
        }
    }
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @link http://home.ubalt.edu/ntsbarsh/Business-stat/otherapplets/pvalues.htm#rtdist
     *
     * @param float $q
     * @param float $i
     * @param float $j
     * @param float $b
     *
     * @return float
     */
    private function statCom($q, $i, $j, $b)
    {
        $zz = 1;
        $z = $zz;
        $k = $i;
        while ($k <= $j) {
            $zz = $zz * $q * $k / ($k - $b);
            $z += $zz;
            $k += 2;
        }
        return $z;
    }
    /**
     * @var int|float $f F statistic.
     */
    private $f;
    /**
     * @var int|float $rSquare R Square.
     */
    private $rSquare;
    /**
     * @var int|double $SSEScalar Sum of squares due to error.
     */
    private $SSEScalar;
    /**
     * @var int|double $SSRScalar Sum of squares due to regression.
     */
    private $SSRScalar;
    /**
     * @var int|double $SSTOScalar Total sum of squares.
     */
    private $SSTOScalar;
    /**
     * @var array $coefficients Regression coefficients array.
     */
    private $coefficients;
    /**
     * @var int|float $multipleR Multiple R.
     */
    private $multipleR;
    /**
     * @var int $observations observations.
     */
    private $observations;
    /**
     * @var array $pValues p values array.
     */
    private $pValues;
    /**
     * @var array $stdErrors Standard error array.
     */
    private $stdErrors;
    /**
     * @var array $tStats t statistics array.
     */
    private $tStats;
    /**
     * @var array $x
     */
    private $x = [];
    /**
     * @var array $y
     */
    private $y = [];
}
