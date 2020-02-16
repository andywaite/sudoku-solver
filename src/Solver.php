<?php

namespace Andywaite\Sudoku;

interface Solver
{
    /**
     * @param Grid $grid
     * @return bool
     */
    public function solve(Grid $grid): bool;
}
