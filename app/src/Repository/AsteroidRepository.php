<?php

namespace App\Repository;

use App\Entity\Asteroid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Asteroid|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asteroid|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asteroid[]    findAll()
 * @method Asteroid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AsteroidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asteroid::class);
    }

    public function findFastest(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('a')
            ->orderBy('a.speed', 'DESC')
            ->setMaxResults(1);
    }

    public function getBestMonth(): array
    {
        $sql        = <<< SQL
select YEAR(`date`) as year, MONTH(`date`) as month, count(id) as `count`
from asteroid a
group by YEAR(`date`), MONTH(`date`)
order by `count` desc
limit 1;
SQL;
        $connection = $this->getEntityManager()->getConnection();
        $statement  = $connection->prepare($sql);
        $statement->execute();

        return $statement->fetch();
    }
}
