<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\AsteroidRepository;
use Symfony\Component\HttpFoundation\Request;
use Exception;

/**
 * @Route("/neo", name="neo_")
 */
class NeoController extends AbstractController
{
    public const ENTRIES_PER_PAGE = 10;

    /** @var EntityManagerInterface */
    private $em;

    /** @var AsteroidRepository */
    private $repository;

    /** @var PaginatorInterface */
    private $paginator;

    public function __construct(
        EntityManagerInterface $em,
        AsteroidRepository $repository,
        PaginatorInterface $paginator
    ) {
        $this->em         = $em;
        $this->repository = $repository;
        $this->paginator  = $paginator;
    }

    /**
     * @Route("/hazardous", name="hazardous", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function hazardous(Request $request): JsonResponse
    {
        $asteroids  = $this->repository->findBy(['isHazardous' => true]);
        $pagination = $this->paginator->paginate(
            $asteroids,
            $request->query->getInt('page', 1),
            self::ENTRIES_PER_PAGE
        );

        return $this->json(
            [
                'data'             => $pagination->getItems(),
                'current_page'     => $pagination->getCurrentPageNumber(),
                'total_entries'    => $pagination->getTotalItemCount(),
                'entries_per_page' => self::ENTRIES_PER_PAGE,
            ]
        );
    }

    /**
     * @Route("/fastest", name="fastest", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fastest(Request $request): JsonResponse
    {
        $asteroids = $this->repository
            ->findFastest()
            ->where('a.isHazardous = :isHazardous')
            ->setParameter('isHazardous', $request->query->getBoolean('hazardous', true))
            ->getQuery()
            ->execute();

        return $this->json(
            [
                'data' => $asteroids,
            ]
        );
    }

    /**
     * @Route("/best-month", name="best_month", methods={"GET"})
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function bestMonth(): JsonResponse
    {
        [
            'year'  => $year,
            'month' => $month,
        ] = $this->repository->getBestMonth();
        $date = new DateTime(sprintf('%s-%s-01', $year, $month));

        return $this->json(
            [
                'date' => $date->format('F, Y'),
            ]
        );
    }
}
