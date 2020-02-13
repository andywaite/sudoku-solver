<?php

namespace Andywaite\Sudoku;

class Grid
{
    protected $grid;

    public function __construct()
    {
        $this->generateGrid();
    }

    protected function generateGrid()
    {
        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 9; $y++) {
                $this->grid[$x][$y] = null;
            }
        }
    }

    /**
     * @param int $x
     * @param int $y
     */
    protected function validateSquareExists(int $x, int $y)
    {
        if (!array_key_exists($x, $this->grid) || !array_key_exists($y, $this->grid[$x])) {
            throw new \OutOfBoundsException("Grid reference not allowed ($x, $y)");
        }
    }

    /**
     * @param int $x
     * @param int $y
     */
    public function nullValue(int $x, int $y)
    {
        $this->grid[$x][$y] = null;
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $value
     */
    public function setValue(int $x, int $y, int $value)
    {
        $this->validateSquareExists($x, $y);
        $this->grid[$x][$y] = $value;
    }

    /**
     * @param int $x
     * @param int $y
     * @return bool
     */
    public function isEmpty(int $x, int $y): bool
    {
        return $this->getValue($x, $y) === null;
    }

    /**
     * @param int $x
     * @param int $y
     * @return int|null
     */
    public function getValue(int $x, int $y): ?int
    {
        $this->validateSquareExists($x, $y);
        return $this->grid[$x][$y];
    }
}
