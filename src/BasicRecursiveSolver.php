<?php

namespace Andywaite\Sudoku;

/**
 * Class Solver
 * @package Andywaite\Sudoku
 *
 * Solves a Sudoku puzzle!
 */
class BasicRecursiveSolver implements Solver
{
    /**
     * @var CellChecker
     */
    private $cellChecker;

    public function __construct(CellChecker $cellChecker)
    {
        $this->cellChecker = $cellChecker;
    }

    /**
     * Attempt to solve sudoku puzzle
     *
     * @param Grid $grid
     * @return bool
     */
    public function solve(Grid $grid): bool
    {
       for ($x = 0; $x < 9; $x++) {
           for ($y = 0; $y < 9; $y++) {

               // Check if cell is empty
               if (!$grid->isEmpty($x, $y)) {
                   continue;
               }

               for ($guess = 1; $guess <= 9; $guess++) {

                   // If this doesn't work, try the next value
                   if (!$this->cellChecker->isValidMove($grid, $x, $y, $guess)) {
                       continue;
                   }

                   // Set value
                   $grid->setValue($x, $y, $guess);

                   // Recursively solve
                   if ($this->solve($grid)) {
                       // Yay, solved!
                       return true;
                   }

                   // Must have failed, backtrack for this cell and try next
                   $grid->nullValue($x, $y);
               }

               // Nothing works in this cell, backtrack
               return false;
           }
       }

        // We won - nowhere left to go!
        return true;
    }
}
