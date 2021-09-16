<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
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

    public function findWithJoins(int $id)
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->andWhere('e.id = :id')->setParameter(':id', $id)

            ->leftJoin('e.etatSortie', 's')->addSelect('s')
            ->leftJoin('e.organisateur', 'o')->addSelect('o')
            ->leftJoin('e.campus', 'c')->addSelect('c')
            ->leftJoin('e.users', 'u')->addSelect('u')
            ->leftJoin('e.lieu', 'l')->addSelect('l');

        return $qb->getQuery()->getResult();
    }

   public function findAllTrips()
   {
       $queryBuilder = $this->createQueryBuilder('t');
       $queryBuilder
           ->leftJoin('t.etatSortie', 's')->addSelect('s')
           ->leftJoin('t.organisateur', 'o')->addSelect('o')
           ->leftJoin('t.users', 'u')->addSelect('u')
           ->leftJoin('t.lieu', 'l')->addSelect('l');
       $query = $queryBuilder -> getQuery();

       $paginator = new Paginator($query);

       return $paginator;

   }

    public function findAllTripsWithFilter(UserInterface $user, ?array $searchData)
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder
            ->leftJoin('t.etatSortie', 's')->addSelect('s')
            ->leftJoin('t.organisateur', 'o')->addSelect('o')
            ->leftJoin('t.users', 'u')->addSelect('u')
            ->leftJoin('t.lieu', 'l')->addSelect('l')
            ->andWhere('s.id != 7')
            ->orderBy('t.dateHeureDebut', 'DESC');


        if (!empty($searchData['keyword'])){
            $queryBuilder->andWhere('t.nom LIKE :kw')
                ->setParameter('kw', '%'.$searchData['keyword'].'%');
        }

        if (!empty($searchData['campus'])){
            $queryBuilder->andWhere('t.campus = :school')
                ->setParameter('school', $searchData['campus']);
        }

        if (!empty($searchData['start_at_min_date'])){
            $queryBuilder->andWhere('t.dateHeureDebut >= :start_at_min_date')
                ->setParameter('start_at_min_date', $searchData['start_at_min_date']);
        }

        if (!empty($searchData['start_at_max_date'])){
            $queryBuilder->andWhere('t.dateHeureDebut <= :start_at_max_date')
                ->setParameter('start_at_max_date', $searchData['start_at_max_date']);
        }


        $filters = $queryBuilder->expr()->orX();

        if (!empty($searchData['is_organizer'])){
            $filters->add($queryBuilder->expr()->eq('o', $user->getId()));
        }

        if (!empty($searchData['subscribed_to'])){
            $filters->add($queryBuilder->expr()->in('u.id', $user->getId()));
        }

        if (!empty($searchData['not_subscribed_to'])) {
            $filters->add($queryBuilder->expr()->notIn('u.id', $user->getId()));
            $filters->add($queryBuilder->expr()->isNull('u.id'));
        }

        if (!empty($searchData['passed_trips'])){
            $filters->add($queryBuilder->expr()->eq('s.id', '5'));
        }


        $queryBuilder->andWhere($filters);

        $query = $queryBuilder -> getQuery();

        $paginator = new Paginator($query);

        return $paginator;

    }




    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
