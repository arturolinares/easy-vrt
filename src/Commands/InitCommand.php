<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InitCommand extends Command
{
    protected static $defaultName = 'vrt:init';

    public function configure()
    {
        $this->setDescription('Prepares backstop worspace on directory "backstop_data');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process([
          'docker',
          'run',
          '--rm',
          '-v',
          __DIR__ . '/../../:/src',
          'backstopjs/backstopjs',
          'init',
        ]);

        /** @var \Symfony\Component\Console\Helper\ProcessHelper */
        $helper = $this->getHelper('process');

        $output->writeln("Initializing backstop...");
        $helper->run($output, $process);

        $output->writeln('Changing backstop.json owner to make it writeable...');
        $chmod = new Process(['sudo', 'chown', '1000',
          realpath(__DIR__ . '/../..') . '/backstop.json']);
        $helper->run($output, $chmod);

        return 0;
    }
}


