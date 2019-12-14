<?php

namespace App\Commands;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    protected static $defaultName = 'vrt:server';

    public function configure()
    {
        $this->setDescription('Starts a server to browse the results');
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The server port.', '8081');
    }

    public function execute(InputInterface $input, OutputInterface $out)
    {
        $root_dir = realpath(__DIR__ . '/../../var/backstop');
        $results_path = 'backstop_data';
        $port = $input->getOption('port');
        $process = new Process(
            ['php', '-S', '0.0.0.0:' . $port, '-t',
            "$root_dir/$results_path"]
        );
        $process->setTimeout(3600);

        $process->start();
        $out->writeln("<comment>There report is here: <href=http://localhost:$port/html_report/index.html>http://localhost:$port/html_report/index.html</></comment>");

        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $out->write("<info>$data</info>");
            }
            else {
                $out->write($data);
            }
        }
        return 0;
    }
}

