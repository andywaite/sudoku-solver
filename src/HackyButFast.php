<?php

namespace Andywaite\Sudoku;

/**
 * Horrible solution - trading maintainability and elegance for SPEED
 *
 * @package Andywaite\Sudoku
 *
 * Solves a Sudoku puzzle!
 */
class HackyButFast implements Solver
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
                        $cells[$i . ":" . $y] = [
                            'x' => $i,
                            'y' => $y
                        ];
                    }

                    if ($i != $y) {
                        $cells[$x . ":" . $i] = [
                            'x' => $x,
                            'y' => $i
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
                            $cells[$i . ":" . $n] = [
                                'x' => $i,
                                'y' => $n
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
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9
        ];

        foreach ($this->peers[$x][$y] as $peer) {
            if (!isset($this->grid[$peer['x']][$peer['y']])) {
                continue;
            }

            $existingValue = $this->grid[$peer['x']][$peer['y']];
            if (isset($candidates[$existingValue])) {
                unset($candidates[$existingValue]);
            }
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
     * Get empty cells in this grid
     *
     * @return array
     */
    protected function getEmptyCells(): array
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
     * Attempt to solve a Sudoku puzzle
     *
     * @param int|null $lastX
     * @param int|null $lastY
     * @return bool
     * @throws \Exception
     */
    protected function recursiveSolve(?int $lastX = null, ?int $lastY = null): bool
    {
        // METHOD 1 - fill in any cells where there's only one option
        $candidates = [];

        // Try searching around the last move to save time
        if ($lastX !== null && $lastY !== null) {
            $peers = $this->peers[$lastX][$lastY];

            foreach ($peers as $peer) {
                // If it's not empty ignore it
                if (!empty($this->grid[$peer['x']][$peer['y']])) {
                    continue;
                }

                // Get moves for this square
                $potentialCandidates = $this->getCandidates($peer['x'], $peer['y']);
                $candidateCount = count($potentialCandidates);

                // Oops, dead end situation - no point carrying on
                if ($candidateCount === 0) {
                    foreach ($candidates as $candidateSoFar) {
                        $this->grid[$candidateSoFar['x']][$candidateSoFar['y']] = null;
                    }
                    return false;
                }

                if ($candidateCount === 1) {
                    $this->grid[$peer['x']][$peer['y']] = $potentialCandidates[0];

                    $candidates[] = [
                        'x' => $peer['x'],
                        'y' => $peer['y'],
                        'value' => $potentialCandidates[0]
                    ];
                }
            }

            if (count($candidates)) {
                // Recursively solve
                $solve = $this->recursiveSolve();

                // Even though this HAS to be right, a previous brute force move may have been wrong, so we may need to backtrack
                if (!$solve) {
                    // Undo all moves and pass up the chain we were wrong :(
                    foreach ($candidates as $candidate) {
                        $this->grid[$candidate['x']][$candidate['y']] = null;
                    }
                    // Backtrack
                    return false;
                }

                // We won!
                return true;
            }
        }


        // METHOD 2 - brute force by trying something that could fit
        $emptyCells = $this->getEmptyCells();
        $optimalCell = [
            'moveCount' => 10
        ];

        // Find out what valid moves we have for each empty cell
        foreach ($emptyCells as $id => $emptyCell) {
            $candidates = $this->getCandidates($emptyCell['x'], $emptyCell['y']);
            $candidateCount = count($candidates);

            // If there's any cell we can't do anything with, we hit a dead end clearly
            if ($candidateCount == 0) {
                return false;
            }

            // We're looking for a move with fewest possible options - speeds things up
            if ($candidateCount < $optimalCell['moveCount']) {
                $optimalCell = $emptyCell;
                $optimalCell['moveCount'] = $candidateCount;
                $optimalCell['moves'] = $candidates;

                if ($candidateCount == 1) {
                    break;
                }
            }
        }

        // If there is a move to be made, let's make it!
        if (isset($optimalCell['moves'])) {
            $x = $optimalCell['x'];
            $y = $optimalCell['y'];

            $validMoves = $optimalCell['moves'];

            // Loop through possible values
            foreach ($validMoves as $try) {
                // Set value
                $this->grid[$x][$y] = $try;

                // Recursively solve
                if ($this->recursiveSolve($x, $y)) {
                    // Yay, solved!
                    return true;
                }

                // Must have failed, backtrack for this cell and try next
                $this->grid[$x][$y] = null;
            }
            // Must be in a bad branch, need to backtrack :(
            return false;
        }

        // Winner winner chicken dinner - nothing left to complete so we must be finished!
        return true;
    }
}
