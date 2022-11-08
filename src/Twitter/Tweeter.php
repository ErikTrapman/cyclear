<?php declare(strict_types=1);

namespace App\Twitter;

class Tweeter
{
    /**
     * @var Twitter
     */
    private $helper;

    private bool $enableTwitter;

    public function __construct(Twitter $helper, bool $enableTwitter)
    {
        $this->helper = $helper;
        $this->enableTwitter = $enableTwitter;
    }

    public function sendTweet($msg): void
    {
        if ($this->enableTwitter) {
            $this->helper->statusesUpdate($msg);
        }
    }
}
