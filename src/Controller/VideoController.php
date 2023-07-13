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
use getID3;


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

        $video->setViews($video->getViews() + 1);
        $em->flush();

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

            // Create UUID
            do {
                $uuid = $this->randomString(10);
                // Check if the uuid is not assigned to any already existing video
                $existingVideo = $em->getRepository(Video::class)->findOneBy(['UUID' => $uuid]);
            } while ($existingVideo);

            $videoFile = $form->get('video')->getData();  // Get file biary data

            if ($videoFile) {
                $filename = $uuid . '.mp4';
                $videoPath = $this->getParameter('videos_directory') . '/' . $filename;

                try {
                    $videoFile->move($this->getParameter('videos_directory'), $filename);
                } catch (FileException $e) {
                    return new Response($e->getMessage(), 500);
                }

                //Create thumbnail

                $thumbnailFilename = $uuid . '.jpg';
                $thumbnailPath = $this->getParameter('thumbnails_directory') . '/' . $thumbnailFilename;

                $ffmpeg = FFMpeg\FFMpeg::create();
                $ffmpegVideo = $ffmpeg->open($this->getParameter('videos_directory') . '/' . $filename);
                $ffmpegVideo
                    ->frame(TimeCode::fromSeconds(0))
                    ->save($thumbnailPath);

                // Get duration
                $id3 = new getID3;
                $fileInfo = $id3->analyze($videoPath);
                $duration = $fileInfo['playtime_string'];


                $title = htmlentities($form->get('title')->getData());
                $description = htmlentities($form->get('description')->getData());

                // Add all data to the entity
                $video->setTitle($title);
                $video->setDescription($description);
                $video->setUUID($uuid);
                $video->setUploadDate(new DateTime());
                $video->setAuthor($currentUser);
                $video->setFile($filename);
                $video->setThumbnail($thumbnailFilename);
                $video->setViews(0);
                $video->setDuration($duration);

                // Save
                $em->persist($video);
                $em->flush();

                return $this->redirectToRoute('app_index');
            }
        }

        return $this->render('video/upload.html.twig', [
            'uploadForm' => $form->createView()
        ]);
    }

    function randomString(int $length, $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"): string
    {
        $charLength = strlen($characters);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, $charLength - 1)];
        }

        return $result;
    }
}
