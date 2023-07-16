<?php

namespace App\MessageHandler;

use App\Entity\Video;
use App\Message\VideoUploadMessage;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\Likelihood;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class VideoUploadMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }


    public function __invoke(VideoUploadMessage $message)
    {
        $authPath = __DIR__ . '/../../google-creds.json';
        // putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $authPath);

        $video = $this->em->getRepository(Video::class)->findOneBy(['id' => $message->getId()]);
        if (!$video) return;

        $videoPath = __DIR__ . '/../../public/videos/' . $video->getFile();
        $thumbnailPath = __DIR__ . '/../../public/img/thumbnails/' . $video->getThumbnail();
        $uri = 'gs://definitely_not_youtube_videos/' . $video->getFile();

        // Temporarily save video in google storage
        $storage = new StorageClient();
        $bucket = $storage->bucket('definitely_not_youtube_videos');
        $storageObject = $bucket->upload(
            fopen($videoPath, 'r')
        );

        $videoClient = new VideoIntelligenceServiceClient();

        # Execute a request.
        $features = [Feature::EXPLICIT_CONTENT_DETECTION];
        $operation = $videoClient->annotateVideo([
            'inputUri' => $uri,
            'features' => $features,
        ]);

        # Wait for the request to complete.
        $operation->pollUntilComplete([
            'pollingIntervalSeconds' => 2
        ]);

        if ($operation->operationSucceeded()) {
            $results = $operation->getResult()->getAnnotationResults()[0];
            $explicitAnnotation = $results->getExplicitAnnotation();
            $explicitContentDetected = false;

            foreach ($explicitAnnotation->getFrames() as $frame) {
                $likelihood = $frame->getPornographyLikelihood();
                $this->logger->debug($likelihood);

                // remove video if it contains explicit content
                if ($likelihood == Likelihood::LIKELY || $likelihood == Likelihood::VERY_LIKELY) {
                    $explicitContentDetected = true;
                    $this->em->remove($video);
                    $this->em->flush();
                    unlink($videoPath);
                    unlink($thumbnailPath);
                    break;
                }
            }

            if (!$explicitContentDetected) {
                $video->setProcessing(0);
                $this->em->flush();
            }
        } else {
            $this->logger->error($operation->getError()->getMessage());
        }

        // Remove video from google storage
        try {
            $storageObject->delete();
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }
}
