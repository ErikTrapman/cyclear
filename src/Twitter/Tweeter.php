<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twitter;

class Tweeter
{
    /**
     *
     * @var Twitter
     */
    private $helper;

    private bool $enableTwitter;

    public function __construct(Twitter $helper, bool $enableTwitter)
    {
        $this->helper = $helper;
        $this->enableTwitter = $enableTwitter;
    }

    public function sendTweet($msg)
    {
        if ($this->enableTwitter) {
            $this->helper->statusesUpdate($msg);
        }
    }
}
