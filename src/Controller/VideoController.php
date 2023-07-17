<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoUpdateFormType;
use App\Form\VideoUploadFormType;
use App\Message\VideoUploadMessage;
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
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class VideoController extends AbstractController
{
    #[Route('/video', name: 'app_video')]
    public function index(Request $request, EntityManagerInterface $em, MessageBusInterface $mb): Response
    {
        $uuid = $request->query->get('v');
        if (!$uuid) {
            return $this->redirectToRoute('app_index');
        }

        $video = $em->getRepository(Video::class)->findOnePublicByUUID($uuid);

        if (!$video) {
            return $this->redirectToRoute('app_index');
        }

        $video->setViews($video->getViews() + 1);
        $em->flush();

        return $this->render('video/index.html.twig', [
            'video' => $video,
        ]);
    }

    #[Route('/video/upload', name: "app_video_upload")]
    public function upload(Request $request, EntityManagerInterface $em, UserInterface $currentUser, MessageBusInterface $mb, LoggerInterface $log): Response
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
                $video->setProcessing(1);

                // Save
                $em->persist($video);
                $em->flush();

                // Run processing process
                $mb->dispatch(new VideoUploadMessage($video->getId()));

                return $this->redirectToRoute('app_account');
            }
        }

        return $this->render('video/upload.html.twig', [
            'uploadForm' => $form->createView()
        ]);
    }

    #[Route('/video/update', name: "app_video_update")]
    function update(Request $req, EntityManagerInterface $em)
    {
        $v = $req->query->get('v');
        if (!$v) return $this->redirectToRoute('app_account');

        $video = $em->getRepository(Video::class)->findOnePublicByUUID($v);
        if (!$video || ($video->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN'))) return $this->redirectToRoute('app_account');

        $form = $this->createForm(VideoUpdateFormType::class, $video);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTitle = htmlentities($form->get('title')->getData());
            $newDescription = htmlentities($form->get('description')->getData());

            $video->setTitle($newTitle);
            $video->setDescription($newDescription);

            $em->flush();

            return $this->redirectToRoute('app_account');
        }

        return $this->render('video/update.html.twig', [
            'updateForm' => $form->createView(),
            'video' => $video
        ]);
    }

    #[Route('/video/remove', name: "app_video_remove")]
    function remove(Request $req, EntityManagerInterface $em)
    {
        $v = $req->query->get('v');
        if (!$v) return $this->redirectToRoute('app_account');

        $videoRep = $em->getRepository(Video::class);

        $video = $videoRep->findOnePublicByUUID($v);
        if (!$video || ($video->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN'))) return $this->redirectToRoute('app_account');

        $thumbnail = __DIR__ . '/../../public/img/thumbnails/' . $video->getThumbnail();
        $videoFile = __DIR__ . '/../../public/videos/' . $video->getFile();

        $videoRep->remove($video, true);
        unlink($thumbnail);
        unlink($videoFile);

        return $this->redirectToRoute('app_account');
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
