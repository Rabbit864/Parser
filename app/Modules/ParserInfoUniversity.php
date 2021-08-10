<?php

namespace App\Modules;

use Symfony\Component\DomCrawler\Crawler;
use App\Models\InfoUniversity;

class ParserInfoUniversity extends ParserUniversity
{
    /**
     * @var string
     */
    private $nameSelector = 'h1.school-headline';

    /**
     * @var string
     */
    private $streetSelector = 'span[itemprop="streetAddress"]';

    /**
     * @var string
     */
    private $locationSelector = 'span[itemprop="addressLocality"]';

    /**
     * @var string
     */
    private $regionSelector = 'span[itemprop="addressRegion"]';

    /**
     * @var string
     */
    private $postalCodeSelector = 'span[itemprop="postalCode"]';

    /**
     * @var string
     */
    private $contactsSelector = '.school-contacts div.row';

    /**
     * @var string
     */
    private $websiteSelector = 'a[itemprop="url"]';

    /**
     * @param mixed $url
     *
     * @return InfoUniversity
     */
    public function parse($url)
    {
        $response = $this->httpClient->get($url);

        if ((string) $response->getBody() === '') {
            return [];
        }

        $crawler = new Crawler(null, $url);
        $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');

        $university = new InfoUniversity();

        $name = $crawler->filter($this->nameSelector);
        $university->name = count($name) > 0 ? trim($name->text()) : '';

        $street = $crawler->filter($this->streetSelector)->text();
        $location = $crawler->filter($this->locationSelector)->text();
        $region = $crawler->filter($this->regionSelector)->text();
        $postalCode = $crawler->filter($this->postalCodeSelector)->text();

        $address = trim($street) . '|' . trim($location) . ',' . trim($region) . '|' . trim($postalCode);
        $university->address = $address;

        $contacts = $crawler->filter($this->contactsSelector)->text();
        $phone = !empty(strstr($contacts, 'Phone')) ? explode(' ', strstr($contacts, 'Phone'))[1] : '';
        $university->phone = $phone;

        $website = count($crawler->filter($this->websiteSelector)) > 0 ? $crawler->filter($this->websiteSelector)->link()->getUri() : '';
        $university->website = $website;

        return $university;
    }

    public function getUrlsPagesUniversities()
    {
        $pageUrls = [];

        foreach($this->getPaginationUrl() as $paginationUrl){
            $crawler = new Crawler(null, $paginationUrl);
            $response = $this->httpClient->get($paginationUrl);
            $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');

            $urls = $crawler->filter('.row .vertical-padding .btn-view-school')->each(function (Crawler $node) {
                return $node->link()->getUri();
            });

            foreach($urls as $url){
                $pageUrls[] = $url;
            }
        }

        return $pageUrls;
    }
}
