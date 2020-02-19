<?php

namespace Andywaite\Sudoku;

/**
 * Horrible solution - trading maintainability and elegance for SPEED
 *
 * @package Andywaite\Sudoku
 *
 * Solves a Sudoku puzzle!
 */
class HackyButFastV2 implements Solver
{

    /**
     * @var array
     */
    protected $peers = [];

    /**
     * @var array
     */
    protected $grid;

    public function __construct()
    {
        $this->setupPeers();
    }

    /**
     * Maintains a list of all linked cells so that when we make 1 move, we can quickly see if that makes other moves possible
     */
    protected function setupPeers()
    {
        for ($x = 0; $x < 9; $x++) {
            for ($y = 0; $y < 9; $y++) {
                $cells = [];

                // Whole X Row and Y row
                for ($i = 0; $i < 9; $i++) {

                    if ($i != $x) {
                        $cells[] = [
                            $i,
                            $y
                        ];
                    }

                    if ($i != $y) {
                        $cells[] = [
                            $x,
                            $i
                        ];
                    }
                }

                // Check for same value in segment
                $segmentXMin = $x - ($x%3);
                $segmentXMax = $segmentXMin + 2;

                $segmentYMin = $y - ($y%3);
                $segmentYMax = $segmentYMin + 2;

                for ($i = $segmentXMin; $i <= $segmentXMax; $i++) {
                    for ($n = $segmentYMin; $n <= $segmentYMax; $n++) {
                        if ($x != $i && $y != $n) {
                            $cells[] = [
                                $i,
                                $n
                            ];
                        }
                    }
                }

                $this->peers[$x][$y] = $cells;
            }
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @return int[]
     */
    protected function getCandidates(int $x, int $y): array
    {
        $candidates = [
           1, 2, 3, 4, 5, 6, 7, 8, 9
        ];

        foreach ($this->peers[$x][$y] as $peer) {
            if (!isset($this->grid[$peer[0]][$peer[1]])) {
                continue;
            }

            unset($candidates[$this->grid[$peer[0]][$peer[1]]- 1]);
        }

        return array_values($candidates);
    }

    /**
     * @param Grid $grid
     * @return bool
     * @throws \Exception
     */
    public function solve(Grid $grid): bool
    {
        $this->grid = $grid->getRawGrid();
        $solved = $this->recursiveSolve();
        $grid->setRawGrid($this->grid);
        return $solved;
    }

    /**
     * Attempt to solve a Sudoku puzzle
     *
     * @param int|null $lastX
     * @param int|null $lastY
     * @return bool
     * @throws \Exception
     */
    protected function recursiveSolve(): bool
    {
        $x = null;
        $y = null;
        $forRollback = [];
        $bestCandidateCount = 10;

        // Find out what valid moves we have for each empty cell
        foreach ($this->grid as $gX => $col) {
            foreach ($col as $gY => $value) {
                if ($value === null) {
                    $candidates = $this->getCandidates($gX, $gY);
                    $candidateCount = count($candidates);

                    // If there's any cell we can't do anything with, we hit a dead end clearly
                    if ($candidateCount === 0) {
                        foreach ($forRollback as $rollback) {
                            $this->grid[$rollback[0]][$rollback[1]] = null;
                        }
                        return false;
                    }

                    // Only one option, do that 1st
                    if ($candidateCount === 1) {
                        $this->grid[$gX][$gY] = $candidates[0];
                        $forRollback[] = [$gX, $gY];
                        continue;
                    }

                    if ($candidateCount < $bestCandidateCount) {
                        $bestCandidateCount = $candidateCount;
                        $x = $gX;
                        $y = $gY;
                    }
                }
            }
        }


        // If there is a move to be made, let's make it!
        if (isset($x)) {
            $optimalCandidates = $this->getCandidates($x, $y);
            // Loop through possible values
            foreach ($optimalCandidates as $try) {
                // Set value
                $this->grid[$x][$y] = $try;

                // Recursively solve
                if ($this->recursiveSolve()) {
                    // Yay, solved!
                    return true;
                }

                // Must have failed, backtrack for this cell and try next
                $this->grid[$x][$y] = null;
            }
            // Must be in a bad branch, need to backtrack :(
            foreach ($forRollback as $rollback) {
                $this->grid[$rollback[0]][$rollback[1]] = null;
            }
            return false;
        }

        // Winner winner chicken dinner - nothing left to complete so we must be finished!
        return true;
    }
}
