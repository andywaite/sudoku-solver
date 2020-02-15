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
    protected $grid;

    /**
     * Count of moves we've made - for debug / optimisation
     *
     * @var int
     */
    protected $moves = 0;

    /**
     * Count of backtracks we've made - for debug / optimisation
     *
     * @var int
     */
    protected $undos = 0;

    public function __construct()
    {
        $this->generateGrid();
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
     * Check we haven't been given junk grid ref
     *
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
     * Undo a move
     *
     * @param int $x
     * @param int $y
     */
    public function nullValue(int $x, int $y)
    {
        $this->undos++;
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
        $this->validateSquareExists($x, $y);
        $this->moves++;
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
        return $this->getValue($x, $y) === null;
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
        $this->validateSquareExists($x, $y);
        return $this->grid[$x][$y];
    }

    /**
     * Count of moves we've made - for debug / optimisation
     *
     * @return int
     */
    public function getMoves(): int
    {
        return $this->moves;
    }

    /**
     * Count of backtracks we've made - for debug / optimisation
     *
     * @return int
     */
    public function getUndos(): int
    {
        return $this->undos;
    }
}
