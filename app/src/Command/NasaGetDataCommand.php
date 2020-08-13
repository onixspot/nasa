<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use DateTimeImmutable;
use App\Service\NasaService;
use Symfony\Component\Form\FormFactoryInterface;

class NasaGetDataCommand extends Command
{
    protected static $defaultName = 'nasa:get-data';

    /** @var NasaService */
    private $service;

    /** @var EntityManagerInterface */
    private $manager;
    /** @var FormFactoryInterface */
    private $formFactory;

    /**
     * NasaGetDataCommand constructor.
     *
     * @param NasaService            $service
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        NasaService $service,
        EntityManagerInterface $manager
    ) {
        parent::__construct(self::$defaultName);
        $this->service     = $service;
        $this->manager     = $manager;
    }

    protected function configure(): void
    {
        $this->addArgument('date', InputArgument::OPTIONAL, 'The end date of request to the NASA API', 'now');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        try {
            $endDate   = new DateTimeImmutable($input->getArgument('date'));
            $startDate = $endDate->modify('-3 days');
            $asteroids = array_filter(
                array_map([$this, 'persistAsteroid'], $this->service->feed($startDate, $endDate)),
                static function (bool $result) {
                    return $result === true;
                }
            );
            $io->success(sprintf('%d asteroids were imported', count($asteroids)));
        } catch (Throwable $e) {
            $io->error("Something wrong: {$e->getMessage()}\n{$e->getTraceAsString()}");
        }

        return 0;
    }

    private function persistAsteroid($asteroid): bool
    {
        try {
            $this->manager->persist($asteroid);
            $this->manager->flush();

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
