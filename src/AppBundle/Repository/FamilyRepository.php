<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Family;

/**
 * FamilyRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FamilyRepository extends EntityRepository
{
    public function listActiveFamilies()
    {
        $dql = "SELECT f as entity, f.id, f.name as name, f.description, f.active as active, count(p.id) as nbProjects, ug.nickname as username, ug.id as user_id
                FROM AppBundle:Family f
                LEFT JOIN AppBundle:Project p
                WITH p.family = f.id
                LEFT JOIN AppBundle\Entity\UserGoogle ug
                WITH f.user = ug.id
                WHERE f.active = 1
                GROUP BY f.name
                ORDER BY f.name
        ";

        return $this->_em
              ->createQuery($dql)
              ->getResult();
    }

    public function listInactiveFamilies()
    {
        $dql = "SELECT f as entity, f.id, f.name as name, f.description, f.active as active, count(p.id) as nbProjects, ug.nickname as username, ug.id as user_id
                FROM AppBundle:Family f
                LEFT JOIN AppBundle:Project p
                WITH p.family = f.id
                LEFT JOIN AppBundle\Entity\UserGoogle ug
                WITH f.user = ug.id
                WHERE f.active = 0
                GROUP BY f.name
                ORDER BY f.name
        ";

        return $this->_em
              ->createQuery($dql)
              ->getResult();
    }

    public function deleteFamily(Family $family)
    {
        $this->_em
           ->createQuery("
            UPDATE AppBundle\Entity\Project p
            SET p.family = NULL
            WHERE p.family = :family
        ")->setParameters([
            'family' => $family,
        ])->execute();

        $this->_em->remove($family);
        $this->_em->flush();
    }
}
