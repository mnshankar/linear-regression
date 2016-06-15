<?php
/**
 * Contains class Matrix.
 *
 * Simple matrix manipulation library.
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

class Matrix
{
    /**
     * Matrix Constructor.
     *
     * Initialize the Matrix object.
     *
     * @param array $matrix The matrix as an array.
     *
     * @throws \InvalidArgumentException Throws exception if jagged array is given.
     */
    public function __construct(array $matrix = [])
    {
        // Insure matrix keys are numeric and start with 0 by using array_values().
        $matrix = array_values($matrix);
        $this->rows = count($matrix);
        $this->columns = count($matrix[0]);
        foreach ($matrix as $i => $row) {
            $row = array_values($row);
            if ($this->columns !== count($row)) {
                throw new \InvalidArgumentException('Invalid matrix');
            }
            $this->mainMatrix[$i] = $row;
        }
    }
    /**
     * Add matrix2 to matrix object that calls this method.
     *
     * @param Matrix $matrix2
     *
     * @return Matrix Note that original matrix is left unchanged
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function add(Matrix $matrix2)
    {
        if (($this->rows !== $matrix2->numRows()) || ($this->columns !== $matrix2->numColumns())) {
            throw new \DomainException('Matrices are not the same size!');
        }
        $newMatrix = [];
        foreach ($this->mainMatrix as $i => $row) {
            foreach ($row as $j => $column) {
                $newMatrix[$i][$j] = $column + $matrix2->getElementAt($i, $j);
            }
        }
        return new Matrix($newMatrix);
    }
    /**
     * Compute the determinant of the square matrix on which this method is called
     *
     * @link http://mathworld.wolfram.com/DeterminantExpansionbyMinors.html
     * @return int|double (depends on input)
     * @throws \RangeException
     * @throws \InvalidArgumentException
     */
    public function determinant()
    {
        if (!$this->isSquareMatrix()) {
            throw new \RangeException('Not a square matrix!');
        }
        $rows = $this->rows;
        $columns = $this->columns;
        $determinant = 0;
        if ($rows === 1 && $columns === 1) {
            return $this->mainMatrix[0][0];
        }
        if ($rows === 2 && $columns === 2) {
            $determinant = $this->mainMatrix[0][0] * $this->mainMatrix[1][1] -
                $this->mainMatrix[0][1] * $this->mainMatrix[1][0];
        } else {
            /** @noinspection ForeachInvariantsInspection */
            for ($j = 0; $j < $columns; ++$j) {
                $subMatrix = $this->getSubMatrix(0, $j);
                if ($j % 2 === 0) {
                    $determinant += $this->mainMatrix[0][$j] * $subMatrix->determinant();
                } else {
                    $determinant -= $this->mainMatrix[0][$j] * $subMatrix->determinant();
                }
            }
        }
        return $determinant;
    }
    /**
     * Display the matrix
     * Formatted display of matrix for debugging.
     */
    public function displayMatrix()
    {
        $rows = $this->rows;
        $cols = $this->columns;
        $debugString = "Order of the matrix is ($rows rows X $cols columns)\n";
        foreach ($this->mainMatrix as $row) {
            $debugString .= implode(', ', $row) . "\n";
        }
        return $debugString;
    }
    /**
     * Return element found at location $row, $col.
     *
     * @param int $row
     * @param int $col
     *
     * @return int|double (depends on input)
     */
    public function getElementAt($row, $col)
    {
        return $this->mainMatrix[$row][$col];
    }
    /**
     * Get the inner array stored in matrix object.
     *
     * @return array
     */
    public function getInnerArray()
    {
        return $this->mainMatrix;
    }
    /**
     * Return the sub-matrix after crossing out the $crossX and $crossY row and column respectively.
     *
     * Part of determinant expansion by minors method.
     *
     * @param int $crossX
     * @param int $crossY
     *
     * @return Matrix
     * @throws \InvalidArgumentException
     */
    public function getSubMatrix($crossX, $crossY)
    {
        $rows = $this->rows;
        $columns = $this->columns;
        $newMatrix = [];
        $p = 0; // sub-matrix row counter
        for ($i = 0; $i < $rows; ++$i) {
            $q = 0; // sub-matrix col counter
            if ($crossX !== $i) {
                for ($j = 0; $j < $columns; ++$j) {
                    if ($crossY !== $j) {
                        $newMatrix[$p][$q] = $this->getElementAt($i, $j);
                        //$matrix[$i][$j];
                        ++$q;
                    }
                }
                ++$p;
            }
        }
        return new Matrix($newMatrix);
    }
    /**
     * Compute the inverse of the matrix on which this method is found (A*A(-1)=I).
     *
     * (cofactor(a))T/(det a)
     *
     * @link http://www.mathwords.com/i/inverse_of_a_matrix.htm
     * @return Matrix
     * @throws \InvalidArgumentException
     * @throws \RangeException
     */
    public function inverse()
    {
        if (!$this->isSquareMatrix()) {
            throw new \RangeException('Not a square matrix!');
        }
        $newMatrix = [];
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $this->rows; ++$i) {
            /** @noinspection ForeachInvariantsInspection */
            for ($j = 0; $j < $this->columns; ++$j) {
                $subMatrix = $this->getSubMatrix($i, $j);
                if (($i + $j) % 2 === 0) {
                    $newMatrix[$i][$j] = $subMatrix->determinant();
                } else {
                    $newMatrix[$i][$j] = -$subMatrix->determinant();
                }
            }
        }
        $cofactorMatrix = new Matrix($newMatrix);
        return $cofactorMatrix->transpose()
                              ->scalarDivide($this->determinant());
    }
    /**
     * Is this a square matrix?
     *
     * Determinants and inverses only exist for square matrices!
     *
     * @return bool
     */
    public function isSquareMatrix()
    {
        return $this->rows === $this->columns;
    }
    /**
     * Multiply matrix2 into matrix object that calls this method.
     *
     * @param Matrix $matrix2
     *
     * @return Matrix Note that original matrix is left unaltered
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function multiply(Matrix $matrix2)
    {
        $columns2 = $matrix2->numColumns();
        if ($this->columns !== $matrix2->numRows()) {
            throw new \DomainException('Incompatible matrix types supplied');
        }
        $newMatrix = [];
        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $this->rows; $i++) {
            /** @noinspection ForeachInvariantsInspection */
            for ($j = 0; $j < $columns2; $j++) {
                $newMatrix[$i][$j] = 0;
                /** @noinspection ForeachInvariantsInspection */
                for ($ctr = 0; $ctr < $this->columns; $ctr++) {
                    $newMatrix[$i][$j] += $this->mainMatrix[$i][$ctr] *
                        $matrix2->getElementAt($ctr, $j);
                }
            }
        }
        return new Matrix($newMatrix);
    }
    /**
     * Number of columns in the matrix
     *
     * @return int
     */
    public function numColumns()
    {
        return count($this->mainMatrix[0]);
    }
    /**
     * Number of rows in the matrix
     *
     * @return int
     */
    public function numRows()
    {
        return count($this->mainMatrix);
    }
    /**
     * Divide every element of matrix on which this method is called by the scalar.
     *
     * @param int|double $scalar
     *
     * @return Matrix
     * @throws \InvalidArgumentException
     */
    public function scalarDivide($scalar)
    {
        if (!is_numeric($scalar)) {
            throw new \InvalidArgumentException('Excepted int or double but given ' . gettype($scalar));
        }
        $newMatrix = [];
        foreach ($this->mainMatrix as $i => $row) {
            foreach ($row as $j => $col) {
                $newMatrix[$i][$j] = $col / $scalar;
            }
        }
        return new Matrix($newMatrix);
    }
    /**
     * Multiply every element of matrix on which this method is called by the scalar.
     *
     * @param int|double $scalar
     *
     * @return Matrix
     * @throws \InvalidArgumentException
     */
    public function scalarMultiply($scalar)
    {
        if (!is_numeric($scalar)) {
            throw new \InvalidArgumentException('Excepted int or double but given ' . gettype($scalar));
        }
        $newMatrix = [];
        foreach ($this->mainMatrix as $i => $row) {
            foreach ($row as $j => $col) {
                $newMatrix[$i][$j] = $col * $scalar;
            }
        }
        return new Matrix($newMatrix);
    }
    /**
     * Subtract matrix2 from matrix object on which this method is called
     *
     * @param Matrix $matrix2
     *
     * @return Matrix Note that original matrix is left unchanged
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function subtract(Matrix $matrix2)
    {
        if (($this->rows !== $matrix2->numRows()) || ($this->columns !== $matrix2->numColumns())) {
            throw new \DomainException('Matrices are not the same size!');
        }
        $newMatrix = [];
        foreach ($this->mainMatrix as $i => $row) {
            foreach ($row as $j => $column) {
                $newMatrix[$i][$j] = $column - $matrix2->getElementAt($i, $j);
            }
        }
        return new Matrix($newMatrix);
    }
    /**
     * Compute the transpose of matrix on which this method is called (invert rows and columns).
     *
     * @return Matrix Original Matrix is not affected.
     * @throws \InvalidArgumentException
     */
    public function transpose()
    {
        $newArray = [];
        foreach ($this->mainMatrix as $i => $row) {
            foreach ($row as $j => $col) {
                $newArray[$j][$i] = $col;
            }
        }
        return new Matrix($newArray);
    }
    /**
     * @var int $columns Num of columns in matrix.
     */
    protected $columns;
    /**
     * @var array $mainMatrix Holds the actual matrix structure.
     */
    protected $mainMatrix;
    /**
     * @var int $rows Num of rows in matrix.
     */
    protected $rows;
}
