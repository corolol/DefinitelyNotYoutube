<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoUploadFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use FFMpeg;
use FFMpeg\Coordinate\TimeCode;

use function AppBundle\Utility\randomString;

class VideoController extends AbstractController
{
    #[Route('/video', name: 'app_video')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $uuid = $request->query->get('v');
        if (!$uuid) {
            return $this->redirectToRoute('app_index');
        }

        $video = $em->getRepository(Video::class)->findOneBy(['UUID' => $uuid]);

        if (!$video) {
            return $this->redirectToRoute('app_index');
        }

        return $this->render('video/index.html.twig', [
            'video' => $video
        ]);
    }

    #[Route('/video/upload', name: "app_video_upload")]
    public function upload(Request $request, EntityManagerInterface $em, UserInterface $currentUser): Response
    {
        $video = new Video();

        $form = $this->createForm(VideoUploadFormType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            do {
                $uuid = randomString(10);
                $existingVideo = $em->getRepository(Video::class)->findOneBy(['UUID' => $uuid]); // Check if the uuid is not assigned to any already existing video
            } while ($existingVideo);

            $videoFile = $form->get('video')->getData();  // Get file biary data
            
            if ($videoFile) {
                $filename = $uuid.'.mp4';

                try {
                    $videoFile->move($this->getParameter('videos_directory'), $filename);
                }
                catch (FileException $e) {
                    return new Response($e->getMessage(), 500);
                }

                //Create thumbnail

                $thumbnailFilename = $uuid.'.jpg';
                $thumbnailPath = $this->getParameter('thumbnails_directory').'/'.$thumbnailFilename;

                $ffmpeg = FFMpeg\FFMpeg::create();
                $ffmpegVideo = $ffmpeg->open($this->getParameter('videos_directory').'/'.$filename);
                $ffmpegVideo
                    ->frame(TimeCode::fromSeconds(0))
                    ->save($thumbnailPath)
                ;

                $video->setTitle($form->get('title')->getData());
                $video->setDescription($form->get('description')->getData());
                $video->setUUID($uuid);
                $video->setUploadDate(new DateTime());
                $video->setAuthor($currentUser);
                $video->setFile($filename);
                $video->setThumbnail($thumbnailFilename);

                $em->persist($video);
                $em->flush();

                return $this->redirectToRoute('app_index');
            }
        }

        return $this->render('video/upload.html.twig', [
            'uploadForm' => $form->createView()
        ]);

    }
}
