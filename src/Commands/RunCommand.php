<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RunCommand extends Command
{
    protected static $defaultName = 'vrt:run';

    public function configure()
    {
        $this->setDescription('Runs backstop reference and then test');
    }

    public function execute(InputInterface $input, OutputInterface $out)
    {
        $out->writeln('<info>Running reference</info>');
        $dir = realpath(__DIR__ . '/../../');
        $refCmd = [ "docker", "run" , "--rm" , "-v" , "$dir:/src" , "backstopjs/backstopjs" , "reference", ];
        $testCmd = [ "docker", "run" , "--rm" , "-v" , "$dir:/src" , "backstopjs/backstopjs" , "test"];
        $helper = $this->getHelper('process');

        $refProcess = new Process($refCmd);
        $helper->run($out, $refProcess);

        $out->writeln('<info>Running test</info>');

        $testProcess = new Process($testCmd);
        $helper->run($out, $testProcess);

        if ($input->getOption('verbose')) {
            $out->write($refProcess->getOutput());
            $out->write($testProcess->getOutput());
        }

        $out->write($testProcess->getOutput());
        return 0;
    }
}
