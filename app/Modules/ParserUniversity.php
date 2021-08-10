<?php

namespace App\Modules;
use Symfony\Component\DomCrawler\Crawler;

abstract class ParserUniversity
{
    /**
     * @var string
     */
    protected $baseUrl = 'https://www.princetonreview.com/college-search?ceid=cp-1022984';

    /**
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();
    }

    /**
     * @return string[]
     */
    public function getPaginationUrl()
    {
        $nextUrl = $this->baseUrl;

        $urlsListUniversity = [];

        $urlsListUniversity[] = $this->baseUrl;

        while (true) {
            $crawler = new Crawler(null, $nextUrl);
            $response = $this->httpClient->get($nextUrl);
            $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');
            $buttonNext = $crawler->filter('.pagination li a')->last();
            if (trim($buttonNext->text()) !== 'Next >') {
                break;
            }
            $urlsListUniversity[] = $buttonNext->link()->getUri();
            $nextUrl = $buttonNext->link()->getUri();
        }

        return $urlsListUniversity;
    }
}
