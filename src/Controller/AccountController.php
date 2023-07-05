<?php

namespace App\Controller;

use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(Request $req, EntityManagerInterface $em, UserInterface $user): Response
    {
        $latestVideo = $em->getRepository(Video::class)->findBy(['author' => $user], ['upload_date' => 'DESC'], 1);
        if (sizeof($latestVideo) > 0) $latestVideo = $latestVideo[0];
        else $latestVideo = null;
        
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'latest_video' => $latestVideo
        ]);
    }
}
