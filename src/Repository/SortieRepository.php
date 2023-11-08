<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        #region recup des infos

        $idSite = $array['idSite'];
        $StringSearch = $array['StringSearch'];
        $DateDebut = $array['DateDebut'];
        $DateFin = $array['DateFin'];
        $organisateur = $array['organisateur'];
        $inscrit = $array['inscrit'];
        $nonInscrit = $array['nonInscrit'];
        $SortiePassee = $array['SortiePassee'];
        $userid = $array['userid'];

        #endregion

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('App\Entity\Sortie', 's')
            ->leftJoin('s.siteOrganisateur', 'site')
            ->leftJoin('s.organisateur', 'organisateur')
            ->leftJoin('s.participants', 'participants');

        #region Filtre

        if ($idSite != 0) {
            $qb->where('s.siteOrganisateur = :idSite')
                ->setParameter('idSite', $idSite);
        }

        if (!empty($StringSearch)) {
            if ($idSite != 0) {
                $qb->andWhere('s.nom LIKE :search')
                    ->setParameter('search', '%' . $StringSearch . '%');
            } else {
                $qb->Where('s.nom LIKE :search')
                    ->setParameter('search', '%' . $StringSearch . '%');
            }
        }

        if ($DateDebut && $DateFin) {

            $DateDebutFormat = new \DateTime($DateDebut);
            $DateFinFormat = new \DateTime($DateFin);

            if ($idSite != 0 || !empty($StringSearch)) {
                $qb->andWhere('s.dateDebut >= :startDate')
                    ->andWhere('s.dateDebut <= :endDate')
                    ->setParameter('startDate', $DateDebutFormat->format('y-m-d h-i-s'))
                    ->setParameter('endDate', $DateFinFormat->format('y-m-d h-i-s'));

            } else {
                $qb->Where('s.dateDebut >= :startDate')
                    ->andWhere('s.dateDebut <= :endDate')
                    ->setParameter('startDate', $DateDebutFormat->format('y-m-d h-i-s'))
                    ->setParameter('endDate', $DateFinFormat->format('y-m-d h-i-s'));
            }
        }

        if ($organisateur) {
            if ($idSite != 0 || !empty($StringSearch) || ($DateDebut && $DateFin)) {
                $qb->andWhere('organisateur = :userId')
                    ->setParameter('userId', $userid);
            } else {
                $qb->Where('organisateur = :userId')
                    ->setParameter('userId', $userid);
            }
        }

        if ($inscrit) {
            if ($idSite != 0 || !empty($StringSearch) || ($DateDebut && $DateFin) || $organisateur) {
                $qb->andWhere(':userId = participants.id')
                    ->setParameter('userId', $userid);
            } else {
                $qb->Where(':userId = participants.id')
                    ->setParameter('userId', $userid);
            }
        }

        if ($nonInscrit) {
            if ($idSite != 0 || !empty($StringSearch) || ($DateDebut && $DateFin) || $organisateur || $inscrit) {
                $qb->andWhere(':userId != participants.id')
                    ->setParameter('userId', $userid);
            } else {
                $qb->Where(':userId != participants.id')
                    ->setParameter('userId', $userid);
            }
        }

        if ($SortiePassee) {
            if ($idSite != 0 || !empty($StringSearch) || ($DateDebut && $DateFin) || $organisateur || $inscrit || $nonInscrit) {
                $qb->andWhere('s.dateDebut < :currentDate')
                    ->setParameter('currentDate', new \DateTime());
            } else {
                $qb->Where('s.dateDebut < :currentDate')
                    ->setParameter('currentDate', new \DateTime());
            }
        }

        #endregion

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
