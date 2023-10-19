<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 *
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    //Pagination
    public function findProductsPaginated(int $current_page, string $slug_categorie, int $limit = 6) : array {
        //Limite est toujour positive
        $limit = abs($limit);
        //le retour est un tableau
        $result = [];
        //la requ_te
        $query = $this->getEntityManager()->createQueryBuilder()
        ->select('c', 'p')
        ->from('App\Entity\Products', 'p')
        ->join('p.categories', 'c')
        ->where("c.slug = '$slug_categorie'")
        ->setMaxResults($limit)
        ->setFirstResult(($current_page * $limit) - $limit);

        //dd($query->getQuery()->getResult());
        //la pagination 
        $paginator = new Paginator($query);
        //Les données
        $data = $paginator->getQuery()->getResult();
        //dd($data);
        //A t'on des données
        if(empty($data)){
            //retourne un tableau vide
            return $result;
        }

        //Calcul du nombre de page = ex 5 / 3 arondis a 2
        $pages = ceil($paginator->count() / $limit);
        //On remplit le tableau
        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page'] = $current_page;
        $result['limit'] = $limit;

        return $result;
    }
}
