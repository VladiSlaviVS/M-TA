<?php

namespace App\Repository;

trait NewsLetterTrait
{
    public function getForNewsLetter(\DateTime $afterDate = null, $hasUpdatedAt = false)
    {

        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.is_active = 1');

        if ($afterDate !== null) {
            $dateFormatted = $afterDate->format('Y-m-d H:i:s');
            if ($hasUpdatedAt) {
                $qb->where('c.created_at > :created OR c.updated_at > :updated')
                    ->setParameter('created', $dateFormatted)
                    ->setParameter('updated', $dateFormatted);
            } else {
                $qb->where('c.created_at > :created')
                    ->setParameter('created', $dateFormatted);
            }
        }

        return $qb->getQuery()->execute();
    }
}