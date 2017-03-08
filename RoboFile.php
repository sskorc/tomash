<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    /** Run tests */
    function test()
    {
        return $this->taskPhpspec();
    }
}
