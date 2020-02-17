<?php

namespace Andywaite\Sudoku;

/**
 * Class Grid
 * @package Andywaite\Sudoku
 *
 * Model to hold our Sudoku grid
 */
class Grid
{
    /**
     * Our grid, represented by a multi-dimensional array - $grid[$x][$y] = $value;
     *
     * @var array
     */
    protected $grid = [];

    public function __construct()
    {
        $this->generateGrid();
    }

    /**
     * @return array
     */
    public function getRawGrid(): array
    {
        return $this->grid;
    }

    /**
     * @param array $grid
     */
    public function setRawGrid(array $grid)
    {
        $this->grid = $grid;
    }

    /**
     * Create an empty grid structure which is 9x9
     */
    protected function generateGrid()
    {
        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 9; $y++) {
                $this->grid[$x][$y] = null;
            }
        }
    }

    /**
     * Undo a move
     *
     * @param int $x
     * @param int $y
     */
    public function nullValue(int $x, int $y)
    {
        $this->grid[$x][$y] = null;
    }

    /**
     * Make a move!
     *
     * @param int $x
     * @param int $y
     * @param int $value
     */
    public function setValue(int $x, int $y, int $value)
    {
        $this->grid[$x][$y] = $value;
    }

    /**
     * Check if cell is empty
     *
     * @param int $x
     * @param int $y
     * @return bool
     */
    public function isEmpty(int $x, int $y): bool
    {
        return $this->grid[$x][$y] === null;
    }

    /**
     * Get empty cells in this grid
     *
     * @return array
     */
    public function getEmptyCells(): array
    {
        $cells = [];

        foreach ($this->grid as $x => $col) {
            foreach ($col as $y => $value) {
                if ($value === null) {
                    $cells[$x.$y] = [
                        'x' => $x,
                        'y' => $y
                    ];
                }
            }
        }

        return $cells;
    }

    /**
     * Get value in cell
     *
     * @param int $x
     * @param int $y
     * @return int|null
     */
    public function getValue(int $x, int $y): ?int
    {
        return $this->grid[$x][$y];
    }
}
