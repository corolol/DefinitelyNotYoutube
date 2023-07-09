<?php

namespace App\Controller;

use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('s');

        if (!$search) return $this->redirectToRoute("app_index");

        $result = $em->getRepository(Video::class)->createQueryBuilder('v')
            ->where('v.title LIKE ');


        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }
}
