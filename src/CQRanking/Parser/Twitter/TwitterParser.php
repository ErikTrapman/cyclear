<?php

/*
 * This file is part of the CQ-ranking parser package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\CQRanking\Parser\Twitter;


use App\CQRanking\Parser\Crawler\CrawlerManager;

class TwitterParser
{


    /**
     * @var CrawlerManager
     */
    private $crawlerManager;

    private $baseUrl;

    public function __construct(CrawlerManager $crawlerManager, $baseUrl)
    {
        $this->crawlerManager = $crawlerManager;
        $this->baseUrl = $baseUrl;
    }

    /**
     *
     * @param int $cqId
     */
    public function getTwitterHandle($cqId)
    {
        $crawler = $this->crawlerManager->getCrawler($this->baseUrl . $cqId);

        return $crawler->filter('table.borderNoOpac')->filterXPath('table[1]')->filter('a')->first()->getNode(0)?->nodeValue;

        foreach ($crawler->filter('table.borderNoOpac')
                     ->filterXPath('table[1]')
                     ->filter('td.textwhite')
                     ->filterXpath('//td[position() = 11]') as $child) {
            return trim($child->nodeValue);
        }
        return null;
    }

}
