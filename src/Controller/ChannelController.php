<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChannelController extends AbstractController
{
    #[Route('/channel', name: 'app_channel')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $username = $request->query->get('u');
        if (!$username) return $this->redirectToRoute('app_index');

        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) return $this->redirectToRoute('app_index');

        $videos = $em->getRepository(Video::class)->findBy(['author' => $user], ['upload_date' => 'DESC']);
        // $videos = $user->getVideos();

        return $this->render('channel/index.html.twig', [
            'user' => $user,
            'videos' => $videos
        ]);
    }
}
