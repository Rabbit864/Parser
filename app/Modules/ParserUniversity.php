<?php

namespace App\Modules;

use App\Models\InfoUniversity;
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

    public function getUrlsPagesUniversities($paginationUrl)
    {
        $crawler = new Crawler(null, $paginationUrl);
        $response = $this->httpClient->get($paginationUrl);
        $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');

        $urls = $crawler->filter('.row .vertical-padding .btn-view-school')->each(function (Crawler $node) {
            return $node->link()->getUri();
        });

        return $urls;
    }

    /**
     * @param mixed $url
     *
     * @return InfoUniversity
     */
    public function parseInfoUniverity($url)
    {
        $response = $this->httpClient->get($url);

        if ((string) $response->getBody() === '') {
            return [];
        }

        $crawler = new Crawler(null, $url);
        $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');

        $university = new InfoUniversity();

        $name = $crawler->filter('h1.school-headline');
        $university->name = count($name) > 0 ? trim($name->text()) : '';

        $street = $crawler->filter('span[itemprop="streetAddress"]')->text();
        $location = $crawler->filter('span[itemprop="addressLocality"]')->text();
        $region = $crawler->filter('span[itemprop="addressRegion"]')->text();
        $postalCode = $crawler->filter('span[itemprop="postalCode"]')->text();

        $address = trim($street) . '|' . trim($location) . ',' . trim($region) . '|' . trim($postalCode);
        $university->address = $address;

        $contacts = $crawler->filter('.school-contacts div.row')->text();
        $phone = !empty(strstr($contacts, 'Phone')) ? explode(' ', strstr($contacts, 'Phone'))[1] : '';
        $university->phone = $phone;

        $website = count($crawler->filter('a[itemprop="url"]')) > 0 ? $crawler->filter('a[itemprop="url"]')->link()->getUri() : '';
        $university->website = $website;

        return $university;
    }

    /**
     * @param string $url
     *
     * @return array array of ListUniversity
     */
    public function parseListUniversities($url)
    {
        $response = $this->httpClient->get($url);

        if ((string) $response->getBody() === '') {
            return [];
        }

        $crawler = new Crawler(null, $url);
        $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');

        $universities = $crawler->filter('.row .vertical-padding')->each(function (Crawler $node) {
            $university = new ListUniversity();

            $name = $node->filter('h2');
            $university->name = count($name) > 0 ? trim($name->text()) : '';

            $image = $node->filter('img');
            $university->linkImage = count($image) > 0 ? trim($image->image()->getUri()) : '';

            $location = count($node->filter('.location')) ? explode(',', $node->filter('.location')->text()) : [];
            $university->city = !empty($location) ? trim($location[0]) : '';
            $university->state = !empty($location) ? trim($location[1]) : '';

            return $university->toArray();
        });

        return $universities;
    }
}
