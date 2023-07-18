<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Video;
use App\Repository\VideoRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CommentController extends AbstractController
{
    #[Route('/comment/post', name: 'app_comment_post', methods: ['POST'])]
    public function create(Request $req, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $user = $this->getUser();
        if (!$user) return new Response('Unauthorized', 401);

        try {
            $body = json_decode($req->getContent(), true);
        } catch (Exception $e) {
            return new Response('Invalid json', 400);
        }

        if (
            !$body ||
            !array_key_exists('content', $body) ||
            !array_key_exists('videoUUID', $body)
        ) return new Response('Bad Request', 400);

        $content = $body['content'];
        $videoUUID = $body['videoUUID'];
        if (!trim($content)) return new Response('Bad Request', 400);

        $video = $em->getRepository(Video::class)->findOnePublicByUUID($videoUUID);
        if (!$video) return new Response('Video not found', 404);

        $comment = new Comment();
        $comment
            ->setAuthor($user)
            ->setContent(trim($content))
            ->setDate(new DateTime())
            ->setVideo($video);

        $em->persist($comment);
        $em->flush();

        $returnData = [
            'content' => $comment->getContent(),
            'username' => $comment->getAuthor()->getUsername(),
            'date' => $comment->getDate()
        ];

        return new Response($serializer->serialize($returnData, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]));
    }

    #[Route('/comment/update', name: 'app_comment_update', methods: ['PUT'])]
    public function update(Request $req, EntityManagerInterface $em) {
        $user = $this->getUser();
        if (!$user) return new Response('Unauthorized', 401);

        try {
            $body = json_decode($req->getContent(), true);
        } catch (Exception $e) {
            return new Response('Invalid json', 400);
        }

        if (
            !$body ||
            !array_key_exists('content', $body) ||
            !array_key_exists('commentID', $body)
        ) return new Response('Bad Request', 400);

        $content = $body['content'];
        $commentID = $body['commentID'];
        if (!trim($content)) return new Response('Bad Request', 400);

        $comment = $em->getRepository(Comment::class)->findOneBy(['id' => $commentID]);
        if (!$comment) return new Response('Bad Request', 400);
        if ($user->getUserIdentifier() != $comment->getAuthor()->getUserIdentifier()) new Response('Forbidden', 403);
        
        $comment->setContent(trim($content));
        $em->flush();

        return new Response();
    }

    #[Route('/comment/delete', name: 'app_comment_delete', methods: ['DELETE'])]
    public function delete(Request $req, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if (!$user) return new Response('Unauthorized', 401);

        try {
            $body = json_decode($req->getContent(), true);
        } catch (Exception $e) {
            return new Response('Invalid json', 400);
        }

        if (
            !$body ||
            !array_key_exists('commentID', $body)
        ) return new Response('Bad Request', 400);

        $comment = $em->getRepository(Comment::class)->findOneBy(['id' => $body['commentID']]);
        if (!$comment) return new Response('Bad Request', 400);

        if ($user->getUserIdentifier() != $comment->getAuthor()->getUserIdentifier()) new Response('Forbidden', 403);

        $em->remove($comment);
        $em->flush();

        return new Response();
    }
}
