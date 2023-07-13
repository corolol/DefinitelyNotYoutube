<?php

namespace App\Controller;

use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function PHPSTORM_META\type;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // $manager->getConnection()->connect();
        // echo $manager->getConnection()->isConnected() ? "P" : "f";
        
        // try {
        //     $manager->getConnection()->isConnected();
        // }
        // catch(Exception $e) {
        //     echo $e;
        // }

        $finder = new Finder();
        $finder->files()->in("img/thumbnails");

        $files = array_keys(iterator_to_array($finder));

        $videos = $em->getRepository(Video::class)->findBy([], ["upload_date" => "DESC"], 24);
        // for ($i = 0; $i < 20; $i++) {
        //     $thumb = $files[random_int(0, sizeof($files)-1)];
        //     $name = "A new way to make pancakes! You won't believe how easy it is! | COOKING WITH TIM [EPISODE #20185309]";
        //     array_push($videos, ["thumb" => $thumb, "name" => $name, "author_name" => "corolol", "views" => 301 ]);
        // }


        return $this->render('index/index.html.twig', ["videos" => $videos]);
    }
}
