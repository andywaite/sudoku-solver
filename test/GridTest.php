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

    public function testSetOutOfBoundsValue()
    {
        $this->expectException("OutOfBoundsException");
        $grid = new Grid();
        $grid->setValue(-1, -1, 3);
    }

    public function testGetOutOfBoundsValue()
    {
        $this->expectException("OutOfBoundsException");
        $grid = new Grid();
        $grid->getValue(-1, -1);
    }

}
