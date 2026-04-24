<?php

namespace App\Plugins\Demo\Application;

use App\Modules\Settings\Application\CheckConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-demo',
    description: 'Initialize demo app'
)]
class AppInitDemoCommand extends Command
{
    public function __construct(
        private readonly AppInitDemoService $appInitDemoService,
        private readonly CheckConfigService $checkConfigService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // init app
        $io->title('Initializing App');
        $this->appInitDemoService->init($io);

        // Check config
        $response = $this->checkConfigService->run($io);
        if ($this->checkConfigService->hasErrors()) {
            $io->error('The configuration has errors');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
