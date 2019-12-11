<?php

namespace App\Commands;

use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateScenarioCommand extends Command
{
    protected static $defaultName = 'vrt:gen';

    protected $scenarioTemplate = [
        "label"=> "",
        "cookiePath"=> "backstop_data/engine_scripts/cookies.json",
        "url"=> "",
        "referenceUrl"=> "",
        "readyEvent"=> "",
        "readySelector"=> "",
        "delay"=> 0,
        "hideSelectors"=> [],
        "removeSelectors"=> [],
        "hoverSelector"=> "",
        "clickSelector"=> "",
        "postInteractionWait"=> 0,
        "selectors"=> [],
        "selectorExpansion"=> true,
        "expect"=> 0,
        "misMatchThreshold"=> 0.1,
        "requireSameDimensions"=> true
    ];

    public function configure()
    {
        $this->setDescription("Generates a backstop config file using the template in the same directory.")
            ->setHelp('Converts a list of routes into a backstop scenario between two environments. The routes are read from a CSV file, where the first column is the route (e.g. /test-url) and the second column is an optional name.')
            ->addArgument('routes', InputArgument::REQUIRED, 'The routes file to transform into scenarios.')
            ->addOption('ref-domain', 'r', InputOption::VALUE_OPTIONAL, 'The reference domain. This will be prepended to the routes to form the reference urls.', '')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'The test domain. This will be prepended to the routes to form the test url.')
            ->addOption('delimiter', 'd', InputOption::VALUE_OPTIONAL, 'Set the CSV delimiter (uses "," by default).', ',')
            ->addOption('backup', 'b', InputOption::VALUE_OPTIONAL, 'If the resulting config file already exists, make a backup before replacing it.', true);
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $templateLocation = __DIR__ . '/../../backstop.template.json';
        $configLocation = __DIR__ . '/../../backstop.json';

        if (!file_exists($templateLocation)) {
            throw new \Exception('The backstop template is missing!');
        }
        $templateContents = file_get_contents($templateLocation);

        /** @var \League\Csv\Reader */
        $csv = Reader::createFromPath($input->getArgument('routes'));
        $csv->setDelimiter($input->getOption('delimiter'));
        $records = $csv->getRecords();
        $scenarios = [];

        $referenceDomain = $this->cleanUrl($input->getOption('ref-domain'));
        $testDomain = $this->cleanUrl($input->getOption('url'));

        foreach ($records as $record) {
            // skip empty routes
            if (!trim($record[0])) {
                continue;
            }

            // Clone the scenario template.
            $new_scenario = json_decode(json_encode($this->scenarioTemplate), true);
            // Set the scenario data pulled from the CSV file.
            $new_scenario['label'] = $record[1] ?? $record[0];
            $new_scenario['referenceUrl'] = $referenceDomain ? $referenceDomain . '/' . $record[0] : '';
            $new_scenario['url'] = $testDomain . '/' . $record[0];
            $scenarios[] = $new_scenario;
        }

        $data = json_decode($templateContents, true);
        $data['scenarios'] = $scenarios;
        // Persist the config file where backstop expects it.
        if (file_exists($configLocation) && $input->getOption('backup')) {
            copy($configLocation, $configLocation . '.' . date('Y-m-d') . '.bak');
        }
        file_put_contents($configLocation, json_encode($data, JSON_PRETTY_PRINT));

        return 0;
    }

    public function cleanUrl(string $url) : string
    {
        if (!preg_match('/^http/', $url)) {
            $url = 'http://' . $url;
        }
        if (preg_match('/\/$/', $url)) {
            $url = substr($url, 0, -1);
        }
        return $url;
    }
}
