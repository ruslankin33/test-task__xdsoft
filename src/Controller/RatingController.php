<?php

namespace App\Controller;

use App\Entity\Rating;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RatingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'app_rating')]
    public function index(Request $request): Response
    {
        $em = $this->entityManager;
        $params = $request->query->all();
        $date = new \DateTimeImmutable($params["date"] ?? time());
        $ratings = $em->getRepository(Rating::class)->findBy(["date" => $date], ["position" => "asc"], 10);


        return $this->render('rating/index.html.twig', [
            "date" => $params["date"],
            "ratings" => $ratings
        ]);
    }
}
