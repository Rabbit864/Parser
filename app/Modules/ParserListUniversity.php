<?php

namespace App\Modules;

use App\Models\ListUniversity;
use App\Modules\ParserUniversity;
use Symfony\Component\DomCrawler\Crawler;

class ParserListUniversity extends ParserUniversity
{

    private $nameSelector = 'h2';

    private $imageSelector = 'img';

    private $locationSelector = '.location';

    /**
     * @param string $url
     *
     * @return array array of ListUniversity
     */
    public function parse($url)
    {
        $response = $this->httpClient->get($url);

        if ((string) $response->getBody() === '') {
            return [];
        }

        $crawler = new Crawler(null, $url);
        $crawler->addHtmlContent((string) $response->getBody(), 'UTF-8');

        $universities = $crawler->filter('.row .vertical-padding')->each(function (Crawler $node) {
            $university = new ListUniversity();

            $name = $node->filter($this->nameSelector);
            $university->name = count($name) > 0 ? trim($name->text()) : '';

            $image = $node->filter($this->imageSelector);
            $university->linkImage = count($image) > 0 ? trim($image->image()->getUri()) : '';

            $location = count($node->filter($this->locationSelector)) ? explode(',', $node->filter($this->locationSelector)->text()) : [];
            $university->city = !empty($location) ? trim($location[0]) : '';
            $university->state = !empty($location) ? trim($location[1]) : '';

            return $university->toArray();
        });

        return $universities;
    }
}
