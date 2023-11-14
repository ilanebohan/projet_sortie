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

    /**
     * @param $array array liste des filtres
     * @return Sortie[] Returns an array of Sortie objects
     */
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
        $statutId = $array['statutId'];

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

        if ($statutId != 0) {
            if ($idSite != 0) {
                $qb->andWhere('s.etat = :idEtat')
                    ->setParameter('idEtat', $statutId);
            } else {
                $qb->Where('s.etat = :idEtat')
                    ->setParameter('idEtat', $statutId);
            }
        }

        if (!empty($StringSearch)) {
            if ($idSite != 0 || $statutId != 0) {
                $qb->andWhere('s.nom LIKE :search')
                    ->setParameter('search', '%' . $StringSearch . '%');
            } else {
                $qb->Where('s.nom LIKE :search')
                    ->setParameter('search', '%' . $StringSearch . '%');
            }
        }

        if ($DateDebut) {

            $DateDebutFormat = new \DateTime($DateDebut);

            if ($idSite != 0  || $statutId != 0 || !empty($StringSearch)) {
                $qb->andWhere('s.dateDebut >= :startDate')
                    ->setParameter('startDate', $DateDebutFormat->format('y-m-d h-i-s'));

            } else {
                $qb->Where('s.dateDebut >= :startDate')
                    ->setParameter('startDate', $DateDebutFormat->format('y-m-d h-i-s'));
            }
        }

        if ($DateFin) {

            $DateFinFormat = new \DateTime($DateFin);

            if ($idSite != 0  || $statutId != 0 || !empty($StringSearch) || $DateDebut) {
                $qb->andWhere('s.dateDebut <= :endDate')
                    ->setParameter('endDate', $DateFinFormat->format('y-m-d h-i-s'));

            } else {
                $qb->Where('s.dateDebut <= :endDate')
                    ->setParameter('endDate', $DateFinFormat->format('y-m-d h-i-s'));
            }
        }



        if ($organisateur || $inscrit || $nonInscrit || $SortiePassee) {

            if ($idSite != 0  || $statutId != 0 || !empty($StringSearch) || $DateDebut || $DateFin) {
                if($nonInscrit) {
                    $subQuery = $em->createQueryBuilder()
                        ->select('s2.id')
                        ->from('App\Entity\Sortie', 's2')
                        ->leftJoin('s2.participants', 'participants2')
                        ->where('participants2.id = :userId');
                }

                $qb->andWhere(
                    $qb->expr()->orX(
                        $organisateur ? $qb->expr()->eq('organisateur', ':userId') : null,
                        $inscrit ? $qb->expr()->eq(':userId', 'participants.id') : null,
                        $nonInscrit ? $qb->expr()->notIn('s.id', $subQuery->getDQL()) : null,
                        $SortiePassee ? $qb->expr()->gte(':currentDate','s.dateDebut') : null
                    )
                );
                if($organisateur || $inscrit)
                     $qb->setParameter('userId', $userid);
                if($SortiePassee)
                    $qb->setParameter('currentDate', new \DateTime());
            } else {
                if($nonInscrit) {
                    $subQuery = $em->createQueryBuilder()
                        ->select('s2.id')
                        ->from('App\Entity\Sortie', 's2')
                        ->leftJoin('s2.participants', 'participants2')
                        ->where('participants2.id = :userId');
                }

                $qb->Where(
                    $qb->expr()->orX(
                        $organisateur ? $qb->expr()->eq('organisateur', ':userId') : null,
                        $inscrit ? $qb->expr()->eq(':userId', 'participants.id') : null,
                        $nonInscrit ? $qb->expr()->notIn('s.id', $subQuery->getDQL()) : null,
                        $SortiePassee ? $qb->expr()->gte(':currentDate','s.dateDebut') : null
                    )
                );
                if($organisateur || $inscrit || $nonInscrit)
                    $qb->setParameter('userId', $userid);
                if($SortiePassee)
                    $qb->setParameter('currentDate', new \DateTime());
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
