<?php

use PHPUnit\Framework\TestCase;
use Andywaite\Sudoku\Grid;

class GridTest extends TestCase
{

    public function testGetSetValue()
    {
        $grid = new Grid();
        $grid->setValue(0, 0, 9);
        $this->assertEquals(9, $grid->getValue(0, 0));
        $this->assertNull($grid->getValue(8, 8));
    }

}
