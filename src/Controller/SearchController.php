<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
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
            ->innerJoin(User::class, 'u', Join::WITH, 'v.author = u.id')
            ->where('LOWER(v.title) LIKE LOWER(:query)')
            ->orWhere('LOWER(u.username) LIKE LOWER(:query)')
            ->addOrderBy('v.upload_date', 'DESC')
            ->setParameter('query', '%'.trim($search).'%')
            ->getQuery()
            ->execute()    
        ;


        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'result' => $result,
        ]);
    }
}
