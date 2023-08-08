<?php

namespace App\Command;

use App\Entity\Movie;
use App\Entity\Rating;
use Doctrine\ORM\EntityManagerInterface;
use voku\helper\HtmlDomParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:rating:get',
    description: 'Get rating from KinoNews.ru',
)]
class GetRatingCommand extends Command
{
    public function __construct(
        private HttpClientInterface    $client,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;
        $dateTime = new \DateTime();
        $path = $_ENV['SITE_PATH'];
        $response = $this->client->request('GET', $path);
        $html = $response->getContent();
        $dom = HtmlDomParser::str_get_html($html);

        $nodes = $dom->find('.block-page-new')->find('div.bigtext');
        foreach ($nodes as $node) {
            $value = trim($node->getNode()->nodeValue);
            $explodeByDot = explode('.', $value);
            $position = (int)trim(array_shift($explodeByDot));
            $value = implode('.', $explodeByDot);
            $explodeByComma = explode(',', $value);
            $year = (int)trim(array_pop($explodeByComma));
            $title = trim(implode(',', $explodeByComma));
            $ratingValue = (float)$node->parentNode()->find('.rating-big')->text()[0];

            if (!empty($position) && !empty($year) && !empty($title) && !empty($ratingValue)) {
                $movie = $em->getRepository(Movie::class)->findOneBy(["title" => $title, "releaseYear" => $year]);
                if (!$movie) {
                    $movie = new Movie();
                    $movie->setTitle($title)->setReleaseYear($year);
                    $em->persist($movie);
                }

                $rating = new Rating();
                $rating
                    ->setMovie($movie)
                    ->setPosition($position)
                    ->setValue($ratingValue)
                    ->setDate($dateTime);

                $em->persist($rating);
            }
        }
        $em->flush();

        return Command::SUCCESS;
    }
}
