<?php

namespace App\Repository;

use App\Entity\Sortie;
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

    public function findByFilter($array) : array
    {
        #region getInfo

        $idSite = $array['idSite'];
        $StringSearch = $array['StringSearch'];
        $DateDebut = $array['DateDebut'];
        $DateFin = $array['DateFin'];
        $organisateur = $array['organisateur'];
        $inscrit = $array['inscrit'];
        $nonInscrit = $array['nonInscrit'];
        $SortiePassee = $array['passee'];
        $userid = $array['userid'];

        #endregion

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('App\Entity\Sortie', 's')
            ->leftJoin('s.site', 'site')
            ->leftJoin('s.organisateur', 'organisateur')
            ->leftJoin('s.inscriptions', 'inscriptions')
            ->where('s.site = :idSite')
            ->setParameter('idSite', $idSite);

        if (!empty($StringSearch)) {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $StringSearch . '%');
        }

        if ($DateDebut && $DateFin) {
            $qb->andWhere('s.dateDebut BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $DateDebut)
                ->setParameter('endDate', $DateFin);
        }

        if ($organisateur) {
            $qb->andWhere('organisateur.id = :userId')
                ->setParameter('userId', $userid);
        }

        if ($inscrit) {
            $qb->andWhere(':userId MEMBER OF inscriptions.participant')
                ->setParameter('userId', $userid);
        }

        if ($nonInscrit) {
            $qb->andWhere(':userId NOT MEMBER OF inscriptions.participant')
                ->setParameter('userId', $userid);
        }

        if ($SortiePassee) {
            $qb->andWhere('s.dateFin < :currentDate')
                ->setParameter('currentDate', new \DateTime());
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
