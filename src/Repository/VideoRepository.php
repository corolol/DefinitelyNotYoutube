<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function save(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOnePublicByUUID($uuid) {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->where($qb->expr()->neq('v.processing', '1'))
            ->andWhere($qb->expr()->eq('v.UUID', ':uuid'))
            ->setParameter('uuid', $uuid)
        ;

        $result = $qb->getQuery()->getResult();
        if (sizeof($result) == 0) return null;
        return $result[0];
    }

    public function findLatestPublicByAuthor(User $user) {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->where($qb->expr()->neq('v.processing', '1'))
            ->andWhere($qb->expr()->eq('v.author', ':author'))
            ->orderBy('v.upload_date', 'DESC')
            ->setParameter('author', $user->getId())
        ;

        $result = $qb->getQuery()->getResult();
        if (sizeof($result) == 0) return null;
        return $result[0];
    }

    public function findPublic(int $limit = 24) {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->where($qb->expr()->neq('v.processing', '1'))
            ->orderBy('v.upload_date', 'DESC')
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getResult();
    }

    public function searchPublic(string $query) {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->innerJoin(User::class, 'u', Join::WITH, 'v.author = u.id')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->neq('v.processing', '1'),
                    $qb->expr()->orX(
                        $qb->expr()->like(
                            $qb->expr()->lower('v.title'),
                            $qb->expr()->lower(':query')
                        ),
                        $qb->expr()->like(
                            $qb->expr()->lower('u.username'),
                            $qb->expr()->lower(':query')
                        )
                    )
                )    
                // 'LOWER() LIKE LOWER(:query) OR LOWER(u.username) LIKE LOWER(:query))'
            )
            ->addOrderBy('v.upload_date', 'DESC')
            ->setParameter('query', '%'.trim($query).'%')
        ;

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Video[] Returns an array of Video objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Video
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
