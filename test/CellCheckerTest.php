<?php

use PHPUnit\Framework\TestCase;
use Andywaite\Sudoku\CellChecker;
use Andywaite\Sudoku\Grid;

class CellCheckerTest extends TestCase
{
    public function testCheckCell()
    {
        $mockGrid = $this->getMockBuilder(Grid::class)->getMock();
        $mockGrid->expects($this->any())->method('getValue')->willReturn(9);

        $cellChecker = new CellChecker();
        $this->assertFalse($cellChecker->isValidMove($mockGrid, 1, 1, 9));
        $this->assertTrue($cellChecker->isValidMove($mockGrid, 1, 1, 2));
    }
}
