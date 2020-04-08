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
        $this->setDescription('Runs backstop and builds the html report.');
    }

    public function execute(InputInterface $input, OutputInterface $out)
    {
        $out->writeln('<info>Running reference</info>');
        $dir = realpath(__DIR__ . '/../../var/backstop');
        $refCmd = [ "docker", "run" , "--rm" , "-v" , "$dir:/src" , "backstopjs/backstopjs" , "reference"];
        $testCmd = [ "docker", "run" , "--rm" , "-v" , "$dir:/src" , "backstopjs/backstopjs" , "test"];

        // If we're in a WSL2 environment, use the default gateway and google
        // DNS to access local and remote sites.
        if (getenv('WSL_DISTRO_NAME')) {
            $refCmd = [ "docker", "run", "--rm", "--dns", "192.168.64.100",
                "--dns", "8.8.8.8", "-v", "$dir:/src", "backstopjs/backstopjs", "reference"];
        }

        $helper = $this->getHelper('process');

        $refProcess = new Process($refCmd);
        $refProcess->setTimeout(1 * 60 * 60); // 1 hr
        $helper->run($out, $refProcess);

        $out->writeln('<info>Running test</info>');

        $testProcess = new Process($testCmd);
        $testProcess->setTimeout(1 * 60 * 60); // 1 hr
        $helper->run($out, $testProcess);

        if ($input->getOption('verbose')) {
            $out->write($refProcess->getOutput());
            $out->write($testProcess->getOutput());
        }

        $out->writeln('<comment>You can browse the results running <info>console vrt:server</info></comment>');
        return 0;
    }
}
