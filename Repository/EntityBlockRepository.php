<?php

namespace Pluetzner\BlockBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pluetzner\BlockBundle\Entity\EntityBlock;
use Pluetzner\BlockBundle\Entity\EntityBlockType;

/**
 * EntityBlockRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class EntityBlockRepository extends EntityRepository
{

    /**
     * @param string $slug
     * @param string $type
     * @return EntityBlock|null
     */
    public function findOneBySlugAndType($slug, $type){
        $qb = $this->createQueryBuilder('eb');

        $eb = $qb
            ->join('eb.entityBlockType', 'entityType')
            ->where('entityType.slug = :type')
            ->andWhere('eb.deleted = :deleted')
            ->andWhere('eb.slug = :slug')
            ->setParameters([
                'type' => $type,
                'slug' => $slug,
                'deleted' => false,
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return $eb;
    }

    /**
     * @param string|EntityBlock $type
     *
     * @return EntityBlock[]
     */
    public function findAllUndeleted($type = null){
        $qb = $this->createQueryBuilder('eb');
        $blocks = $qb
            ->join('eb.entityBlockType', 'entityType')
            ->where('eb.deleted = 0')
            ->orderBy('eb.orderId', 'DESC');

        if(true === is_string($type)) {
            $blocks
                ->andWhere('entityType.slug = :type')
                ->setParameter('type', $type);
        } elseif (EntityBlockType::class === get_class($type)) {
              $blocks
                ->andWhere('entityType.slug = :type')
                ->setParameter('type', $type->getSlug());
        }

        return $blocks->getQuery()->getResult();
    }
}
