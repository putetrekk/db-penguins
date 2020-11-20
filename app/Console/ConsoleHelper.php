<?php

namespace App\Console;

use Symfony\Component\Console\Helper\ProgressBar;

class ConsoleHelper
{
    /**
     * Create and return a progressbar
     * @return ProgressBar
     */
    public static function ProgressBar($output, $max)
    {
        $progress = new ProgressBar($output, $max);
        $progress->setBarCharacter('<info>=</info>');
        $progress->setEmptyBarCharacter('=');
        $progress->setProgressCharacter('>');
        return $progress;
    }
}
