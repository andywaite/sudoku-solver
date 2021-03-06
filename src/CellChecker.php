<?php

namespace Andywaite\Sudoku;

/**
 * Class CellChecker
 * @package Andywaite\Sudoku
 *
 * Helper class to perform mass functions across our Grid
 */
class CellChecker
{
    /**
     * @var array
     */
    protected $peers = [];

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
     * @param Grid $grid
     * @param int $x
     * @param int $y
     * @return int[]
     */
    public function getCandidates(Grid $grid, int $x, int $y): array
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
            if ($grid->isEmpty($peer['x'], $peer['y'])) {
                continue;
            }

            $existingValue = $grid->getValue($peer['x'], $peer['y']);
            if (isset($candidates[$existingValue])) {
                unset($candidates[$existingValue]);
            }
        }

        return array_values($candidates);
    }

    /**
     * Get cells affected by our last move (to check if we have any new obvious moves)
     *
     * These are keyed with the x:y to help us avoid checking the same cell twice later
     *
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getPeers(int $x, int $y): array
    {
        return $this->peers[$x][$y];
    }

    /**
     * @param Grid $grid
     * @param int $x
     * @param int $y
     * @param $value
     * @return bool
     */
    public function isValidMove(Grid $grid, int $x, int $y, $value): bool
    {
        foreach ($this->peers[$x][$y] as $linkedCell) {

            // We can't have the same value as one of our peers
            if ($grid->getValue($linkedCell['x'], $linkedCell['y']) == $value) {
                return false;
            }
        }

        return true;
    }
}
