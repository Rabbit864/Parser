<?php

namespace App\Modules;

use App\Models\ListUniversity;
use Symfony\Component\DomCrawler\Crawler;

class ParserUniversity
{
    /**
     * @var string
     */
    private $baseUrl = 'https://www.princetonreview.com/college-search?ceid=cp-1022984';

    /**
     * @var GuzzleHttp\Client
     */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();
    }

    /**
     * @return string[]
     */
    private function parsePaginationUrl()
    {
        $nextUrl = $this->baseUrl;
        $urlsListUniversity = [$this->baseUrl];
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

    public function parseListUniversities()
    {
        $urls = $this->parsePaginationUrl();

        foreach ($urls as $url) {
            $response = $this->httpClient->get($url);

            $crawler = new Crawler(null, $url);
            $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');

            $universities = $crawler->filter('.row .vertical-padding')->each(function (Crawler $node) {
                $university = new ListUniversity();

                $university->name = trim($node->filter('h2')->text());

                $image = $node->filter('img');
                $university->linkImage = count($image) > 0 ? trim($image->image()->getUri()) : '';

                $location = count($node->filter('.location')) ? explode(',', $node->filter('.location')->text()) : [];
                $university->city = !empty($location) ? trim($location[0]) : '';
                $university->state = !empty($location) ? trim($location[1]) : '';

                return $university->toArray();
            });
            ListUniversity::insert($universities);
        }
    }
}