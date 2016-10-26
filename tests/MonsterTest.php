<?php
require_once 'vendor/autoload.php';
//debug_print_backtrace();
use PHPUnit\Framework\TestCase;
/**
 * Created by PhpStorm.
 * User: nathanieldavidson
 * Date: 10/25/16
 * Time: 3:25 PM
 */
include "Monster.php";
class MonsterTest extends TestCase
{
    public function testThisMethod()
    {
        // Arrange
        $a = new Monster();
        // Assert
        $this->assertEquals("werewolf", $a->pop());
    }
}