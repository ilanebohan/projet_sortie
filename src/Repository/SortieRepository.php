<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SebastianBergmann\Environment\Console;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilter($array): array
    {
        $idSite = $array['idSite'];
        $StringSearch = $array['StringSearch'];
        $DateDebut = $array['DateDebut'];
        $DateFin = $array['DateFin'];
        $organisateur = $array['organisateur'];
        $inscrit = $array['inscrit'];
        $nonInscrit = $array['nonInscrit'];
        $SortiePassee = $array['SortiePassee'];
        $userid = $array['userid'];

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('App\Entity\Sortie', 's')
            ->leftJoin('s.siteOrganisateur', 'site')
            ->leftJoin('s.organisateur', 'organisateur')
            ->leftJoin('s.inscriptions', 'inscriptions');

        if($idSite != 0) {
            $qb->where('s.siteOrganisateur = :idSite')
                ->setParameter('idSite', $idSite);
        }

        if (!empty($StringSearch)) {
            if($idSite != 0) {
                $qb->andWhere('site.nom LIKE :search')
                    ->setParameter('search', '%' . $StringSearch . '%');
            }else
            {
                $qb->Where('site.nom LIKE :search OR s.nom LIKE :search')
                    ->setParameter('search', '%' . $StringSearch . '%');
            }
        }

        if ($DateDebut && $DateFin) {
            if($idSite != 0  || !empty($StringSearch)) {
                $qb->andWhere('s.dateDebut BETWEEN :startDate AND :endDate')
                    ->setParameter('startDate', new \DateTime($DateDebut))
                    ->setParameter('endDate', new \DateTime($DateFin));
            }else
            {
                $qb->Where('s.dateDebut BETWEEN :startDate AND :endDate')
                    ->setParameter('startDate', new \DateTime($DateDebut))
                    ->setParameter('endDate', new \DateTime($DateFin));
            }
        }

        if ($organisateur) {
            if($idSite != 0 || !empty($StringSearch) || ($DateDebut || $DateFin)) {
                $qb->andWhere('organisateur.id = :userId')
                    ->setParameter('userId', $userid);
            }else
            {
                $qb->Where('organisateur.id = :userId')
                    ->setParameter('userId', $userid);
            }
        }

        if ($inscrit) {
            if($idSite != 0 || !empty($StringSearch) || ($DateDebut || $DateFin) || $organisateur) {
                $qb->andWhere(':user MEMBER OF inscriptions.participants')
                    ->setParameter('user', $em->getReference(User::class, $userid));
            }else
            {
                $qb->Where(':user MEMBER OF inscriptions.participants')
                    ->setParameter('user', $em->getReference(User::class, $userid));
            }
        }

        if ($nonInscrit) {
            if($idSite != 0 || !empty($StringSearch) || ($DateDebut || $DateFin) || $organisateur || $inscrit) {
                $qb->andWhere(':user NOT MEMBER OF inscriptions.participants')
                    ->setParameter('user', $em->getReference(User::class, $userid));
            }else{
                $qb->Where(':user NOT MEMBER OF inscriptions.participants')
                    ->setParameter('user', $em->getReference(User::class, $userid));
            }
        }

        if ($SortiePassee) {
            if($idSite != 0 || !empty($StringSearch) || ($DateDebut || $DateFin) || $organisateur || $inscrit || $nonInscrit) {
                $qb->andWhere('s.dateDebut < :currentDate')
                    ->setParameter('currentDate', new \DateTime());
            }else
            {
                $qb->Where('s.dateDebut < :currentDate')
                    ->setParameter('currentDate', new \DateTime());
            }
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
