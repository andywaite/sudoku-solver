<?php

namespace Andywaite\Sudoku;

class GridLoader
{
    /**
     * Load grid from file
     *
     * @param Grid $grid
     * @param string $filename
     */
    public function loadGrid(Grid $grid, string $filename)
    {
        $fileContents = file_get_contents($filename);
        $cols = explode("\n", $fileContents);
        foreach ($cols as $y => $col) {
            $rows = str_split($col, 1);
            foreach ($rows as $x => $val) {
                if (!empty($val) && is_numeric($val)) {
                    $grid->setValue($x, $y, $val);
                }
            }
        }
    }
}
