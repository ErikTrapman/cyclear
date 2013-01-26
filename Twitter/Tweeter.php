<?php

namespace Cyclear\GameBundle\Twitter;

class Tweeter
{
    private $helper;

    public function __construct(\TijsVerkoyen\Twitter\Twitter $helper)
    {
        $this->helper = $helper;
    }

    public function sendTweet($msg)
    {
        $this->helper->statusesUpdate($msg);
    }
}