<?php

namespace App\Command;

use App\Controller\CurrencyApiController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\LockableTrait;

class GetCurrencyRatesCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'currency:get-rates';

    protected function configure(): void
    {
        $this->setDescription('Gets currency rates from API');
        $this->addArgument('mode', InputArgument::REQUIRED, 'For which days do we need data? Values: all | today');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return Command::SUCCESS;
        }

        $output->writeln('Get currency rates & save to database');
        $mode = $input->getArgument('mode');

        $currencyApi = new CurrencyApiController();
        if ($currencyApi->process($mode)) {
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
